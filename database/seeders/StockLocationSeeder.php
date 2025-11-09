<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StockLocation;

class StockLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the main warehouse
        StockLocation::firstOrCreate(
            ['code' => 'WH-MAIN'],
            [
                'name' => 'Main Warehouse (General Stock)',
                'address' => 'Your company address',
                'type' => 'warehouse',
                'is_active' => true
            ]
        );
    }
}