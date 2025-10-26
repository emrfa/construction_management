<?php

namespace App\Http\Controllers;

use App\Models\StockTransaction;

use Illuminate\Http\Request;


class StockLedgerController extends Controller
{
    public function index()
    {
        // Get all transactions, newest first, and load the item details
        $transactions = StockTransaction::with(['item', 'sourceable'])
                                        ->latest('created_at') // Order by when the transaction was created
                                        ->paginate(25); // Paginate results for performance

        return view('stock-ledger.index', compact('transactions'));
    }
}
