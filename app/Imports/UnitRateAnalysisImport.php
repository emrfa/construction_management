<?php

namespace App\Imports;

use App\Models\UnitRateAnalysis;
use App\Models\InventoryItem;
use App\Models\LaborRate;
use App\Models\Equipment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // <-- ADD THIS
use Maatwebsite\Excel\Validators\ValidationException; // <-- ADD THIS

class UnitRateAnalysisImport implements ToCollection, WithHeadingRow, WithBatchInserts
{
    private $inventoryItems;
    private $laborRates;
    private $equipments;
    private $currentAhs;
    private $fixMap; 
    private $errors = []; // <-- ADD THIS
    private $rowCounter = 1; // <-- ADD THIS (start at 1 for header)

    public function __construct(array $fixMap = [])
    {
        $this->fixMap = $fixMap;
        $this->inventoryItems = InventoryItem::all()->pluck('id', fn($item) => strtolower($item->item_name));
        $this->laborRates = LaborRate::all()->pluck('id', fn($rate) => strtolower($rate->labor_type));
        $this->equipments = Equipment::all()->pluck('id', fn($eq) => strtolower($eq->name));
        $this->currentAhs = null;
    }

    public function collection(Collection $rows)
    {
        DB::beginTransaction();
        try {
            foreach ($rows as $row) 
            {
                $this->rowCounter++; // Increment row counter

                // Is this a new AHS header row?
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
                    $this->currentAhs->equipments()->delete();
                } 
                // Is this a component row?
                else if ($this->currentAhs && !empty($row['component_type'])) {
                    
                    $originalName = $row['component_name_used_for_match'];
                    if(empty($originalName)) continue; // Skip if name is blank

                    $originalNameLower = strtolower($originalName);
                    $resolvedName = $this->fixMap[$originalNameLower] ?? $originalNameLower;
                    
                    if ($resolvedName === 'skip') {
                        continue;
                    }

                    $found = false;

                    if ($row['component_type'] == 'Material') {
                        $materialId = $this->inventoryItems->get($resolvedName);
                        if ($materialId) {
                            $this->currentAhs->materials()->create([
                                'inventory_item_id' => $materialId,
                                'coefficient' => $row['coefficient'],
                                'unit_cost' => $row['component_unit_cost'],
                            ]);
                            $found = true;
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
                            $found = true;
                        }
                    }
                    else if ($row['component_type'] == 'Equipment') {
                        $equipmentId = $this->equipments->get($resolvedName);
                        if ($equipmentId) {
                            $this->currentAhs->equipments()->create([
                                'equipment_id' => $equipmentId,
                                'coefficient' => $row['coefficient'],
                                'cost_rate' => $row['component_unit_cost'],
                            ]);
                            $found = true;
                        }
                    }

                    // --- THIS IS THE NEW ERROR CHECKING ---
                    // If we didn't find a match (and it wasn't skipped), it's an error.
                    if (!$found) {
                        $this->addError("The component name '$originalName' was not found or resolved.");
                    }
                }
            }

            // Recalculate the very last AHS
            if ($this->currentAhs) {
                $this->currentAhs->recalculateTotalCost();
            }

            // --- THROW ALL ERRORS AT THE END ---
            if (!empty($this->errors)) {
                // We use a ValidationException so the controller can catch it
                throw ValidationException::withMessages($this->errors);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e; // Re-throw the exception to be caught by the controller
        }
    }

    // This helper function adds errors with the correct row number
    private function addError(string $message)
    {
        $this->errors[] = "Row " . $this->rowCounter . ": " . $message;
    }
    
    // We remove the complex `rules()` method, as we are now
    // handling validation manually in the `collection()` method.
    public function rules(): array
    {
        return [
            'ahs_code' => 'nullable|string',
            'component_type' => 'nullable|in:Material,Labor,Equipment',
            'coefficient' => 'nullable|numeric|min:0',
            'component_unit_cost' => 'nullable|numeric|min:0',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }
}