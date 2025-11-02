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
    private $currentAhs;

    public function __construct()
    {
        // Cache all items/rates by NAME to avoid N+1 queries
        // This assumes names are unique!
        $this->inventoryItems = InventoryItem::pluck('id', 'item_name');
        $this->laborRates = LaborRate::pluck('id', 'labor_type');
        $this->currentAhs = null;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) 
            {
                // Is this a new AHS header row?
                if (!empty($row['ahs_code'])) {
                    
                    // If we were processing an AHS, recalculate it before moving on
                    if ($this->currentAhs) {
                        $this->currentAhs->recalculateTotalCost();
                    }

                    // 1. Find or create the new AHS Header
                    $this->currentAhs = UnitRateAnalysis::updateOrCreate(
                        ['code' => $row['ahs_code']],
                        [
                            'name' => $row['ahs_name'],
                            'unit' => $row['ahs_unit'],
                            'overhead_profit_percentage' => $row['ahs_overhead_profit_percentage'] ?? 0,
                        ]
                    );

                    // 2. Clear out all old components to prepare for new ones
                    $this->currentAhs->materials()->delete();
                    $this->currentAhs->labors()->delete();

                } 
                // Is this a component row for the current AHS?
                else if ($this->currentAhs && !empty($row['component_type'])) {
                    
                    if ($row['component_type'] == 'Material') {
                        // 3. Find the material ID by its NAME
                        $materialId = $this->inventoryItems->get($row['component_name_used_for_match']);
                        if ($materialId) {
                            $this->currentAhs->materials()->create([
                                'inventory_item_id' => $materialId,
                                'coefficient' => $row['coefficient'],
                                'unit_cost' => $row['component_unit_cost'],
                            ]);
                        }
                    } 
                    else if ($row['component_type'] == 'Labor') {
                        // 4. Find the labor ID by its NAME (type)
                        $laborId = $this->laborRates->get($row['component_name_used_for_match']);
                        if ($laborId) {
                            $this->currentAhs->labors()->create([
                                'labor_rate_id' => $laborId,
                                'coefficient' => $row['coefficient'],
                                'rate' => $row['component_unit_cost'], // Map 'unit_cost' to 'rate'
                            ]);
                        }
                    }
                }
                // Otherwise, it's a blank row, so we ignore it
            }

            // 5. Recalculate the very last AHS item in the file
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
            'component_unit_cost' => 'nullable|numeric|min:0',

            // Validation by NAME
            'component_name_used_for_match' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return; // Ignore if blank

                    $row = request()->all(); // Get the row data
                    if ($row['component_type'] == 'Material' && !$this->inventoryItems->has($value)) {
                        $fail("The material name '$value' was not found in the Item Master.");
                    }
                    if ($row['component_type'] == 'Labor' && !$this->laborRates->has($value)) {
                        $fail("The labor type '$value' was not found in Labor Rates.");
                    }
                },
            ],
        ];
    }

    public function batchSize(): int
    {
        return 200; // Increase batch size
    }
}