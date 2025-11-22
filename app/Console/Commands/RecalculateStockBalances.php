<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RecalculateStockBalances extends Command
{
    protected $signature = 'stock:recalculate';
    protected $description = 'Recalculate Stock Balances (WAC) from Transaction History';

    public function handle()
    {
        $this->info('Starting Stock Balance Recalculation...');

        // 1. Truncate existing balances
        \App\Models\StockBalance::truncate();
        $this->info('Truncated stock_balances table.');

        // 2. Fetch all transactions ordered by ID (chronological)
        // We assume ID order is sufficient for chronological replay.
        $transactions = \App\Models\StockTransaction::orderBy('id')->cursor();

        $bar = $this->output->createProgressBar(\App\Models\StockTransaction::count());
        $bar->start();

        foreach ($transactions as $tx) {
            $balance = \App\Models\StockBalance::firstOrNew([
                'inventory_item_id' => $tx->inventory_item_id,
                'stock_location_id' => $tx->stock_location_id,
            ]);

            $currentQty = $balance->quantity ?? 0;
            $currentAvg = $balance->average_unit_cost ?? 0;
            
            $txQty = $tx->quantity;
            $txCost = $tx->unit_cost;

            if ($txQty > 0) {
                // INCOMING: Update WAC
                // New Value = (OldQty * OldAvg) + (NewQty * NewCost)
                // New Qty = OldQty + NewQty
                // New Avg = New Value / New Qty
                
                $totalValue = ($currentQty * $currentAvg) + ($txQty * $txCost);
                $totalQty = $currentQty + $txQty;

                $balance->quantity = $totalQty;
                if ($totalQty > 0) {
                    $balance->average_unit_cost = $totalValue / $totalQty;
                }
            } else {
                // OUTGOING: Keep WAC, Reduce Qty
                // Note: We do NOT update the transaction's unit_cost here because that would rewrite history.
                // We are only rebuilding the *current* state. 
                // Ideally, we should check if the transaction cost matches the WAC at that time, but that's too complex for now.
                
                $balance->quantity += $txQty; // txQty is negative
            }

            $balance->last_transaction_id = $tx->id;
            $balance->save();
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Stock Balance Recalculation Completed Successfully.');
    }
}
