<?php

namespace App\Exports;

use App\Models\InventoryItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryItemsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $ids;

    /**
    * Pass an optional array of IDs to export only those items.
    * If no IDs are provided, it exports all.
    */
    public function __construct(array $ids = null)
    {
        $this->ids = $ids;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Start the query
        $query = InventoryItem::with('itemCategory');

        // If IDs were provided, only get those.
        if ($this->ids) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get();
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        // These are the column headers in the Excel file
        return [
            'item_code',
            'item_name',
            'category_name',
            'uom',
            'base_purchase_price',
            'reorder_level',
            'stock_on_hand',
        ];
    }

    /**
    * @param mixed $item
    * @return array
    */
    public function map($item): array
    {
        // This maps each item's data to the columns
        return [
            $item->item_code,
            $item->item_name,
            $item->itemCategory->name ?? 'N/A', // Get name from relationship
            $item->uom,
            $item->base_purchase_price,
            $item->reorder_level,
            $item->quantity_on_hand, // Access the helper attribute
        ];
    }
}