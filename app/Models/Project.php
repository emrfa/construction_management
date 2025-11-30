<?php

namespace App\Models;

use App\Models\QuotationItem;
use App\Models\PurchaseOrder;
use App\Models\StockLocation; // <-- Make sure this is here

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
        'location',
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

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            $year = date('Y');
            $lastProject = DB::table('projects')
                               ->where('project_code', 'LIKE', "P-{$year}-%")
                               ->orderBy('project_code', 'desc')
                               ->first();
            $number = 1;
            if ($lastProject) {
                $number = (int)substr($lastProject->project_code, -4) + 1;
            }
            $project->project_code = "P-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }

    public function billings()
    {
        return $this->hasMany(Billing::class);
    }

    public function materialRequests()
    {
        return $this->hasMany(MaterialRequest::class);
    }

    public function adendums()
    {
        return $this->hasMany(Adendum::class);
    }

    /**
     * Get the dedicated stock location for this project.
     */
    public function stockLocation()
    {
        return $this->hasOne(StockLocation::class)->where('type', 'site');
    }

    /**
     * Get all stock transactions for this project's location.
     */
    public function stockTransactions()
    {
        return $this->hasManyThrough(
            StockTransaction::class,
            StockLocation::class,
            'project_id', // Foreign key on StockLocation table
            'stock_location_id', // Foreign key on StockTransaction table
            'id', // Local key on Project table
            'id'  // Local key on StockLocation table
        );
    }

    /**
     * UPDATED: getMaterialStockSummary
     * This was broken and is now fixed to use the location ID.
     */
    public function getMaterialStockSummary(): Collection
    {
        // Get the project's dedicated location ID
        $projectLocationId = $this->stockLocation?->id;

        // Get all stock transactions for *this* project's location
        $projectStock = StockTransaction::where('stock_location_id', $projectLocationId)
                             ->groupBy('inventory_item_id')
                             ->select('inventory_item_id', DB::raw('SUM(quantity) as on_hand_qty'))
                             ->get()
                             ->keyBy('inventory_item_id');

        // Get all "on order" items for *this* project
        $projectPOs = $this->purchaseOrders()
            ->whereIn('status', ['ordered', 'partially_received'])
            ->with('items')
            ->get();
            
        $onOrderQuantities = collect();
        foreach ($projectPOs as $po) {
            foreach ($po->items as $poItem) {
                $qtyOutstanding = (float)$poItem->quantity_ordered - (float)$poItem->quantity_received;
                if ($qtyOutstanding > 0) {
                    $onOrderQuantities[$poItem->inventory_item_id] = ($onOrderQuantities[$poItem->inventory_item_id] ?? 0) + $qtyOutstanding;
                }
            }
        }

        // Get all "used" items on *this* project
        $usageTotals = MaterialUsage::whereHas('progressUpdate.quotationItem.quotation', fn($q) => $q->where('id', $this->quotation_id))
            ->select('inventory_item_id', DB::raw('SUM(quantity_used) as total_used'))
            ->groupBy('inventory_item_id')
            ->get()
            ->keyBy('inventory_item_id');

        // Get all "budgeted" items for *this* project
        $this->loadMissing('quotation.allItems.unitRateAnalysis.materials.inventoryItem');
        
        $materialSummary = collect();
        
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
                                $currentItem['budgeted_qty'] += $budgetedQtyForItem;
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

        // --- START OF FIX ---
        // We can't modify the collection with $materialSummary[$itemId]['key'] = ...
        // We have to get the item, modify it, and put() it back.
        foreach ($materialSummary as $itemId => $details) {
            // 1. Get the current item (which is an array)
            $currentItem = $materialSummary->get($itemId); 
            
            // 2. Modify the array
            $currentItem['used_qty'] = $usageTotals->get($itemId)?->total_used ?? 0;
            $currentItem['on_order_qty'] = $onOrderQuantities->get($itemId) ?? 0;
            $currentItem['on_hand_qty'] = $projectStock->get($itemId)?->on_hand_qty ?? 0;

            // 3. Put the modified array back into the collection
            $materialSummary->put($itemId, $currentItem);
        }
        // --- END OF FIX ---

        return $materialSummary->sortBy('item_code')->values();
    }

    public function getActualCostAttribute()
    {
        $this->loadMissing('quotation.items');
        if (!$this->quotation) {
            return 0;
        }
        return $this->quotation->items->sum('actual_cost');
    }

    public function getBudgetLeftAttribute()
    {
        return (float)$this->total_budget - (float)$this->getActualCostAttribute();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Check if the project is ready for performance reporting.
     * Returns true if status is appropriate AND schedule data exists.
     */
    public function isReadyForReport()
    {
        // 1. Status Check
        if (!in_array($this->status, ['in_progress', 'completed', 'closed'])) {
            return false;
        }

        // 2. Schedule Check
        // We need either project-level dates OR task-level dates
        $hasProjectDates = $this->start_date && $this->end_date;
        
        if ($hasProjectDates) {
            return true;
        }

        // If no project dates, check if any tasks have dates
        // We load the relationship if not already loaded to avoid N+1 if called in loop (though ideally eager loaded)
        $this->loadMissing('quotation.allItems');
        
        if ($this->quotation && $this->quotation->allItems) {
            $hasTaskDates = $this->quotation->allItems->whereNotNull('planned_start')->isNotEmpty();
            return $hasTaskDates;
        }

        return false;
    }
}