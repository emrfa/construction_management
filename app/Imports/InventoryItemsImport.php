<?php

namespace App\Imports;

use App\Models\InventoryItem;
use App\Models\ItemCategory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class InventoryItemsImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts
{
    private $categories;

    public function __construct()
    {
        // 1. We cache all categories in a collection (key=name, value=id)
        // This avoids hitting the database for every single row (N+1 problem).
        $this->categories = ItemCategory::all()->pluck('id', 'name');
    }

    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            // 2. Look up the category_id from the name in the Excel file
            $categoryId = $this->categories->get($row['category_name']);

            // 3. We use updateOrCreate.
            // If an item_code in the file matches one in the DB, it updates it.
            // If it's a new item_code, it creates it.
            InventoryItem::updateOrCreate(
                [
                    // Column to match on
                    'item_code' => $row['item_code']
                ],
                [
                    // Data to update or create
                    'item_name'           => $row['item_name'],
                    'category_id'         => $categoryId,
                    'uom'                 => $row['uom'],
                    'base_purchase_price' => $row['base_purchase_price'] ?? 0,
                    'reorder_level'       => $row['reorder_level'] ?? 0,
                ]
            );

            // Note: If 'item_code' is blank, updateOrCreate treats it as a 'create'.
            // The boot() method in your InventoryItem model will then
            // auto-generate a new code. This is exactly what we want.
        }
    }

    /**
     * This defines the validation rules for each row.
     */
    public function rules(): array
    {
        return [
            'item_code' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
            'category_name' => 'required|string|exists:item_categories,name', // Force valid category names
            'uom' => 'required|string|max:50',
            'base_purchase_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * This improves performance by importing in batches.
     */
    public function batchSize(): int
    {
        return 100;
    }
}