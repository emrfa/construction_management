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
    private $fixMap; // <-- ADD THIS

    // --- MODIFIED CONSTRUCTOR ---
    public function __construct(array $fixMap = [])
    {
        $this->fixMap = $fixMap;
        $this->categories = ItemCategory::all()->pluck('id', function($category) {
            return strtolower($category->name);
        });
    }

    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            $categoryName = $row['category_name'];
            $categoryNameLower = strtolower($categoryName);
            
            // --- THIS IS THE NEW LOGIC ---
            // 1. Check if this category was a problem
            $resolvedName = $this->fixMap[$categoryNameLower] ?? $categoryNameLower;

            // 2. User said to skip rows with this category
            if ($resolvedName === 'skip') {
                continue;
            }

            // 3. Find the category ID using the resolved name
            $categoryId = $this->categories->get($resolvedName);
            
            // 4. If we still don't have an ID (because it was just created),
            //    we must re-fetch it from the database.
            if (!$categoryId) {
                 $newCategory = ItemCategory::where(DB::raw('LOWER(name)'), $resolvedName)->first();
                 if ($newCategory) {
                     $categoryId = $newCategory->id;
                     // Add to our cache for the next row
                     $this->categories->put($resolvedName, $categoryId);
                 } else {
                     continue; // Should not happen, but a good safeguard
                 }
            }

            // 5. We use updateOrCreate.
            InventoryItem::updateOrCreate(
                [
                    'item_code' => $row['item_code']
                ],
                [
                    'item_name'           => $row['item_name'],
                    'category_id'         => $categoryId, // Use the resolved ID
                    'uom'                 => $row['uom'],
                    'base_purchase_price' => $row['base_purchase_price'] ?? 0,
                    'reorder_level'       => $row['reorder_level'] ?? 0,
                ]
            );
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
            'category_name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $nameLower = strtolower($value);
                    
                    // Check if the name is in our fix map or already exists
                    if (isset($this->fixMap[$nameLower]) || $this->categories->has($nameLower)) {
                        return; // This name is valid or has been resolved
                    }
                    
                    // If we're here, it's an unresolved problem
                    $fail("The category '$value' was not found and was not resolved.");
                }
            ],
            'uom' => 'required|string|max:50',
            'base_purchase_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }
}