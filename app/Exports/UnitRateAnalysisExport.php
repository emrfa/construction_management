<?php

namespace App\Exports;

use App\Models\UnitRateAnalysis;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UnitRateAnalysisExport implements FromCollection, WithHeadings
{
    protected $ids;

    public function __construct(array $ids = null)
    {
        $this->ids = $ids;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = UnitRateAnalysis::with('materials.inventoryItem', 'labors.laborRate');

        if ($this->ids) {
            $query->whereIn('id', $this->ids);
        }

        $ahsItems = $query->orderBy('code')->get();
        $excelRows = collect();

        foreach ($ahsItems as $ahs) {
            // 1. Add the main AHS Header Row
            $excelRows->push([
                'ahs_code' => $ahs->code,
                'ahs_name' => $ahs->name,
                'ahs_unit' => $ahs->unit,
                'ahs_overhead_profit_percentage' => $ahs->overhead_profit_percentage,
                'component_type' => '',
                'component_code' => '',
                'component_name' => '',
                'coefficient' => '',
                'component_unit_cost' => '',
            ]);

            // 2. Add Material rows
            foreach ($ahs->materials as $material) {
                if ($material->inventoryItem) {
                    $excelRows->push([
                        'ahs_code' => '',
                        'ahs_name' => '',
                        'ahs_unit' => '',
                        'ahs_overhead_profit_percentage' => '',
                        'component_type' => 'Material',
                        'component_code' => $material->inventoryItem->item_code,
                        'component_name' => $material->inventoryItem->item_name,
                        'coefficient' => $material->coefficient,
                        'component_unit_cost' => $material->unit_cost,
                    ]);
                }
            }
            // 3. Add Labor rows
            foreach ($ahs->labors as $labor) {
                if ($labor->laborRate) {
                    $excelRows->push([
                        'ahs_code' => '',
                        'ahs_name' => '',
                        'ahs_unit' => '',
                        'ahs_overhead_profit_percentage' => '',
                        'component_type' => 'Labor',
                        'component_code' => '',
                        'component_name' => $labor->laborRate->labor_type, 
                        'coefficient' => $labor->coefficient,
                        'component_unit_cost' => $labor->rate,
                    ]);
                }
            }
        }

        return $excelRows;
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'ahs_code',
            'ahs_name',
            'ahs_unit',
            'ahs_overhead_profit_percentage',
            'component_type',
            'component_code (For Reference)',
            'component_name (Used for Match)',
            'coefficient',
            'component_unit_cost',
        ];
    }
}