<?php

namespace App\Imports;

use App\Models\UnitRateAnalysis;
use App\Models\InventoryItem;
use App\Models\LaborRate;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\DB;

class UnitRateAnalysisImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts
{
    private $inventoryItems;
    private $laborRates;
    private $currentAhs; // To store the AHS we are processing
    private $fixMap;

    public function __construct(array $fixMap = [])
    {
        $this->fixMap = $fixMap;
        $this->inventoryItems = InventoryItem::all()->pluck('id', function($item) {
            return strtolower($item->item_name);
        });
        $this->laborRates = LaborRate::all()->pluck('id', function($rate) {
            return strtolower($rate->labor_type);
        });
        $this->currentAhs = null;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) 
            {
                if (!empty($row['ahs_code'])) {
                    if ($this->currentAhs) {
                        $this->currentAhs->recalculateTotalCost();
                    }
                    $this->currentAhs = UnitRateAnalysis::updateOrCreate(
                        ['code' => $row['ahs_code']],
                        [
                            'name' => $row['ahs_name'],
                            'unit' => $row['ahs_unit'],
                            'overhead_profit_percentage' => $row['ahs_overhead_profit_percentage'] ?? 0,
                        ]
                    );
                    $this->currentAhs->materials()->delete();
                    $this->currentAhs->labors()->delete();

                } 
                else if ($this->currentAhs && !empty($row['component_type'])) {
                    
                    $originalName = strtolower($row['component_name_used_for_match']);
                    
                    // --- APPLY THE FIX MAP ---
                    $resolvedName = $this->fixMap[$originalName] ?? $originalName;
                    
                    // Skip this row if user told us to
                    if ($resolvedName === 'skip') {
                        continue;
                    }

                    if ($row['component_type'] == 'Material') {
                        $materialId = $this->inventoryItems->get($resolvedName);
                        if ($materialId) {
                            $this->currentAhs->materials()->create([
                                'inventory_item_id' => $materialId,
                                'coefficient' => $row['coefficient'],
                                'unit_cost' => $row['component_unit_cost'],
                            ]);
                        }
                    } 
                    else if ($row['component_type'] == 'Labor') {
                        $laborId = $this->laborRates->get($resolvedName);
                        if ($laborId) {
                            $this->currentAhs->labors()->create([
                                'labor_rate_id' => $laborId,
                                'coefficient' => $row['coefficient'],
                                'rate' => $row['component_unit_cost'],
                            ]);
                        }
                    }
                }
            }

            if ($this->currentAhs) {
                $this->currentAhs->recalculateTotalCost();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            // Header rows
            'ahs_code' => 'nullable|string',
            'ahs_name' => 'nullable|string',
            'ahs_unit' => 'nullable|string',
            'ahs_overhead_profit_percentage' => 'nullable|numeric|min:0',

            // Component rows
            'component_type' => 'nullable|in:Material,Labor',
            'coefficient' => 'nullable|numeric|min:0',
            'component_unit_cost' => 'nullable|numeric|min:0', // This is the correct key

            // Validation by NAME
            'component_name_used_for_match' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;
                    $row = request()->all(); 
                    if (empty($row['component_type'])) return;
                    
                    $nameLower = strtolower($value);

                    // --- CHECK THE FIX MAP ---
                    // If the name is in our fix map, it's already resolved.
                    if (isset($this->fixMap[$nameLower])) {
                        if ($this->fixMap[$nameLower] === 'skip') {
                            return; // User wants to skip this, so don't fail
                        }
                        // Use the corrected name for validation
                        $nameLower = $this->fixMap[$nameLower];
                    }

                    // Now validate with the (potentially corrected) name
                    if ($row['component_type'] == 'Material' && !$this->inventoryItems->has($nameLower)) {
                        $fail("The material name '$value' was not found or resolved.");
                    }
                    if ($row['component_type'] == 'Labor' && !$this->laborRates->has($nameLower)) {
                        $fail("The labor type '$value' was not found or resolved.");
                    }
                },
            ],
        ];
    }

    public function batchSize(): int
    {
        return 200; 
    }
}