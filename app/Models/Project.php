<?php

namespace App\Models;

use App\Models\QuotationItem;
use App\Models\PurchaseOrder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'client_id',
        'project_code',
        'start_date',
        'end_date',
        'actual_end_date',
        'status',
        'total_budget',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'actual_end_date' => 'datetime',
    ];

    // A Project belongs to one Quotation
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    // A Project belongs to one Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate project_code when creating a new project
        static::creating(function ($project) {
            // Get the current year
            $year = date('Y');
            
            // Get the last project number for this year
            $lastProject = DB::table('projects')
                               ->where('project_code', 'LIKE', "P-{$year}-%")
                               ->orderBy('project_code', 'desc')
                               ->first();

            $number = 1;
            if ($lastProject) {
                // Extract the last number and increment it
                $number = (int)substr($lastProject->project_code, -4) + 1;
            }

            // Format the new project number (e.g., P-2025-0001)
            $project->project_code = "P-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    // A Project can have many Billings
    public function billings()
    {
        return $this->hasMany(Billing::class);
    }

    public function materialRequests()
    {
        return $this->hasMany(MaterialRequest::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(\App\Models\StockTransaction::class);
    }

    public function getMaterialStockSummary()
    {
        return \App\Models\InventoryItem::with(['stockTransactions' => function ($q) {
            $q->where('project_id', $this->id);
        }])->get()->map(function ($item) {
            return [
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'uom'       => $item->uom,
                'quantity'  => $item->stockTransactions->sum('quantity'),
            ];
        })->filter(fn($r) => $r['quantity'] != 0);
    }

    public function getActualCostAttribute()
    {
        // 1. Eager load the root quotation items.
        // We already fixed the 'actual_cost' on the QuotationItem model,
        // so we can just trust its calculation.
        $this->loadMissing('quotation.items');

        if (!$this->quotation) {
            return 0;
        }

        // 2. Sum the 'actual_cost' attribute of all root items.
        // Each root item will recursively sum its children,
        // giving us the total for the entire project.
        return $this->quotation->items->sum('actual_cost');
    }

    // HELPER: Get remaining project budget
    public function getBudgetLeftAttribute()
    {
        // Ensure total_budget and actual_cost are treated as numbers
        return (float)$this->total_budget - (float)$this->getActualCostAttribute();
    }
    /**
     * Generates a detailed summary of materials based on WBS requirements for the project.
     *
     * @return Collection
     */
    public function getWbsMaterialSummary(): Collection
    {
        $this->loadMissing([
            'quotation.allItems.unitRateAnalysis.materials.inventoryItem',
            'quotation.allItems.progressUpdates.materialUsages',
            'purchaseOrders.items.inventoryItem', // Ensure PO relationship exists
            'stockTransactions.item'
        ]);

        $materialSummary = collect();

        // 1. Calculate Budgeted Quantities
        if ($this->quotation) {
            foreach ($this->quotation->allItems as $wbsItem) {
                if ($wbsItem->children->isEmpty() && $wbsItem->unitRateAnalysis && $wbsItem->unitRateAnalysis->materials->isNotEmpty()) {
                    $wbsQuantity = (float)($wbsItem->quantity ?? 0);
                    foreach ($wbsItem->unitRateAnalysis->materials as $ahsMaterial) {
                        if ($ahsMaterial->inventoryItem) {
                            $inventoryItemId = $ahsMaterial->inventory_item_id;
                            $coefficient = (float)($ahsMaterial->coefficient ?? 0);
                            $budgetedQtyForItem = $wbsQuantity * $coefficient;

                            if ($materialSummary->has($inventoryItemId)) {
                                $currentItem = $materialSummary->get($inventoryItemId);
                                $currentItem['budgeted_qty'] += $budgetedQtyForItem; // Fix applied
                                $materialSummary->put($inventoryItemId, $currentItem);
                            } else {
                                $materialSummary->put($inventoryItemId, [
                                    'item_id'       => $inventoryItemId,
                                    'item_code'     => $ahsMaterial->inventoryItem->item_code,
                                    'item_name'     => $ahsMaterial->inventoryItem->item_name,
                                    'uom'           => $ahsMaterial->inventoryItem->uom,
                                    'budgeted_qty'  => $budgetedQtyForItem,
                                    'used_qty'      => 0,
                                    'on_order_qty'  => 0,
                                    'on_hand_qty'   => 0,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        // 2. Calculate Used Quantities
        if ($this->quotation) {
            foreach($this->quotation->allItems as $wbsItem) {
                if($wbsItem->progressUpdates->isNotEmpty()) {
                    foreach($wbsItem->progressUpdates as $update) {
                        foreach($update->materialUsages as $usage) {
                            $inventoryItemId = $usage->inventory_item_id;
                            if($materialSummary->has($inventoryItemId)) {
                                // --- Apply Get/Modify/Put here ---
                                $currentItem = $materialSummary->get($inventoryItemId);
                                $currentItem['used_qty'] += (float)$usage->quantity_used; // Fix applied
                                $materialSummary->put($inventoryItemId, $currentItem);
                                // --- End fix ---
                            }
                            // Else: material used wasn't budgeted, ignore for now
                        }
                    }
                }
            }
        }

        // 3. Calculate On Order Quantities
        if ($this->purchaseOrders->isNotEmpty()) { // Check the relationship exists
            foreach ($this->purchaseOrders as $po) {
                if (in_array($po->status, ['ordered', 'partially_received'])) {
                    foreach ($po->items as $poItem) {
                        $inventoryItemId = $poItem->inventory_item_id;
                        $qtyOutstanding = (float)$poItem->quantity_ordered - (float)$poItem->quantity_received;
                        if ($qtyOutstanding > 0 && $materialSummary->has($inventoryItemId)) {
                             // --- Apply Get/Modify/Put here ---
                             $currentItem = $materialSummary->get($inventoryItemId);
                             $currentItem['on_order_qty'] += $qtyOutstanding; // Fix applied
                             $materialSummary->put($inventoryItemId, $currentItem);
                             // --- End fix ---
                        }
                        // Else: PO item wasn't budgeted, ignore for now
                    }
                }
            }
        }

        // 4. Calculate On Hand Quantities (Specific to this Project)
        $projectStock = $this->stockTransactions
                             ->groupBy('inventory_item_id')
                             ->map(fn ($transactions) => $transactions->sum('quantity'));

        foreach ($projectStock as $inventoryItemId => $quantity) {
             if ($materialSummary->has($inventoryItemId)) {
                 // --- Apply Get/Modify/Put here ---
                 $currentItem = $materialSummary->get($inventoryItemId);
                 $currentItem['on_hand_qty'] = (float)$quantity; // Fix applied (direct assignment is ok)
                 $materialSummary->put($inventoryItemId, $currentItem);
                 // --- End fix ---
            }
        }

        return $materialSummary->sortBy('item_code')->values();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function laborUsages()
    {
        return $this->hasMany(LaborUsage::class);
    }
}
