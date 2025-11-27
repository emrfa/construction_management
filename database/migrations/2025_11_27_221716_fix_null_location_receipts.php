<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\StockLocation;
use App\Models\GoodsReceipt;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Find the fallback location (WH-001)
        $fallbackLocationId = StockLocation::where('code', 'WH-001')->value('id');

        // If WH-001 doesn't exist, try to find ANY active location
        if (!$fallbackLocationId) {
            $fallbackLocationId = StockLocation::where('is_active', true)->value('id');
        }

        if ($fallbackLocationId) {
            // 2. Update all receipts with NULL location
            $count = GoodsReceipt::whereNull('stock_location_id')->update(['stock_location_id' => $fallbackLocationId]);
            echo "Updated {$count} receipts with NULL location to Location ID: {$fallbackLocationId}\n";
        } else {
            echo "No valid fallback location found. Skipping update.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed as we are fixing data integrity
    }
};
