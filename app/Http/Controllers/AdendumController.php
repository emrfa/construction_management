<?php

namespace App\Http\Controllers;

use App\Models\Adendum;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdendumController extends Controller
{
    public function index(Project $project)
    {
        $adendums = $project->adendums()->with('items')->latest()->get();
        return view('projects.adendums.index', compact('project', 'adendums'));
    }

    public function create(Project $project)
    {
        // Get quotation items (Leaf nodes only) for "Deduct" or "Add to Existing"
        $quotationItems = $project->quotation 
            ? $project->quotation->allItems()->doesntHave('children')->get() 
            : collect([]);

        // Get AHS items for "Add New" functionality
        $ahsItems = \App\Models\UnitRateAnalysis::orderBy('name')->get();

        return view('projects.adendums.create', compact('project', 'quotationItems', 'ahsItems'));
    }

    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_extension_days' => 'nullable|integer|min:0',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric',
            'items.*.unit_price' => 'required|numeric',
            'items.*.uom' => 'nullable|string',
            'items.*.quotation_item_id' => 'nullable|exists:quotation_items,id',
        ]);

        // Validation: Check if Deductions are valid (not exceeding executed quantity)
        foreach ($validated['items'] as $index => $item) {
            if (isset($item['quotation_item_id']) && $item['quantity'] < 0) {
                if ($item['quantity'] < 0) {
                    $deductQty = abs($item['quantity']);
                    $quotationItem = \App\Models\QuotationItem::find($item['quotation_item_id']);
                    
                    if ($quotationItem) {
                        $executedQty = $quotationItem->quantity * ($quotationItem->latest_progress / 100);
                        $remainingQty = $quotationItem->quantity - $deductQty;

                        if ($remainingQty < $executedQty) {
                            // Fail validation
                            return back()->withInput()->withErrors([
                                "items.{$index}.quantity" => "Cannot deduct {$deductQty} {$quotationItem->uom}. Executed quantity is " . number_format($executedQty, 2) . " {$quotationItem->uom}."
                            ]);
                        }
                    }
                }
            }
        }

        DB::transaction(function () use ($validated, $project) {
            // Generate Adendum No
            $count = $project->adendums()->count() + 1;
            $adendumNo = 'ADD-' . $project->project_code . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            $adendum = $project->adendums()->create([
                'adendum_no' => $adendumNo,
                'date' => $validated['date'],
                'subject' => $validated['subject'],
                'description' => $validated['description'] ?? null,
                'time_extension_days' => $validated['time_extension_days'] ?? 0,
                'status' => 'draft',
            ]);

            foreach ($validated['items'] as $itemData) {
                $subtotal = $itemData['quantity'] * $itemData['unit_price'];
                
                $adendum->items()->create([
                    'quotation_item_id' => $itemData['quotation_item_id'] ?? null,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'uom' => $itemData['uom'] ?? null,
                    'subtotal' => $subtotal,
                ]);
            }

            $adendum->calculateTotal();
        });

        return redirect()->route('projects.adendums.index', $project)
            ->with('success', 'Adendum created successfully.');
    }

    public function show(Project $project, Adendum $adendum)
    {
        $adendum->load('items.quotationItem');
        return view('projects.adendums.show', compact('project', 'adendum'));
    }

    public function approve(Project $project, Adendum $adendum)
    {
        if ($adendum->status !== 'draft') {
            return back()->with('error', 'Only draft adendums can be approved.');
        }

        DB::transaction(function () use ($project, $adendum) {
            // 1. Update Project Schedule
            if ($adendum->time_extension_days > 0) {
                $project->end_date = $project->end_date->addDays($adendum->time_extension_days);
                $project->save();
            }

            // 2. Process Items
            foreach ($adendum->items as $item) {
                if ($item->quotation_item_id) {
                    // --- EXISTING ITEM (Add or Deduct) ---
                    $originalItem = \App\Models\QuotationItem::find($item->quotation_item_id);
                    if ($originalItem) {
                        // Snapshot Original Quantity (if not already set)
                        if (is_null($originalItem->original_quantity)) {
                            $originalItem->original_quantity = $originalItem->quantity;
                            $originalItem->original_subtotal = $originalItem->subtotal;
                        }

                        // Calculate Executed Quantity BEFORE update
                        $currentProgress = $originalItem->latest_progress;
                        $executedQty = $originalItem->quantity * ($currentProgress / 100);

                        // Update Quantity (Add or Deduct based on sign)
                        // If item->quantity is negative (Deduct), this reduces originalItem->quantity.
                        // If item->quantity is positive (Add), this increases originalItem->quantity.
                        $originalItem->quantity += $item->quantity;
                        
                        // Recalculate Subtotal
                        $originalItem->subtotal = $originalItem->quantity * $originalItem->unit_price;
                        $originalItem->save();

                        // [FIX] Update Parent Subtotals (Rollup)
                        $this->updateParentSubtotals($originalItem);

                        // Auto-adjust Progress if there was progress
                        if ($currentProgress > 0 && $originalItem->quantity > 0) {
                            $newProgress = ($executedQty / $originalItem->quantity) * 100;
                            
                            // Cap at 100% just in case (though validation prevents over-deduction)
                            $newProgress = min(100, max(0, $newProgress));

                            $originalItem->progressUpdates()->create([
                                'user_id' => auth()->id() ?? 1, // Fallback to ID 1 if no auth
                                'date' => $adendum->date,
                                'percent_complete' => $newProgress,
                                'notes' => "Auto-adjusted due to Adendum {$adendum->adendum_no} (Qty changed from " . ($originalItem->quantity - $item->quantity) . " to {$originalItem->quantity})",
                            ]);
                        }
                    }
                } else {
                    // --- NEW ITEM (Add Work) ---
                    $project->quotation->items()->create([
                        'quotation_id' => $project->quotation->id,
                        'parent_id' => null, // Root item
                        'description' => $item->description . ' (Adendum ' . $adendum->adendum_no . ')',
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'uom' => $item->uom,
                        'subtotal' => $item->subtotal,
                        'original_quantity' => 0, // New work has 0 original scope
                        'original_subtotal' => 0,
                        'adendum_id' => $adendum->id,
                        'sort_order' => 999, // Force to bottom
                    ]);
                }
            }

            // 3. Update Quotation Total - REMOVED to keep Quotation as Original Contract
            $newTotal = $project->quotation->items()->sum('subtotal');
            // $project->quotation->update(['total_estimate' => $newTotal]);

            // 4. Update Project Budget
            $project->update(['total_budget' => $newTotal]);

            // 5. Mark Adendum as Approved
            $adendum->update(['status' => 'approved']);
        });

        return back()->with('success', 'Adendum approved. WBS updated with Revised Quantities.');
    }

    /**
     * Recursively update parent subtotals.
     */
    private function updateParentSubtotals($item)
    {
        if ($item->parent_id) {
            $parent = $item->parent;
            if ($parent) {
                // Recalculate parent subtotal from its children
                $parent->subtotal = $parent->children()->sum('subtotal');
                $parent->save();

                // Recurse up
                $this->updateParentSubtotals($parent);
            }
        }
    }
}
