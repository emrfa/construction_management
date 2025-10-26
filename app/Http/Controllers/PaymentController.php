<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Store a newly created payment resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Invoice $invoice)
    {
        // Use $invoice->remaining_balance which calculates correctly
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->remaining_balance,
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // *** FIX: Refresh the invoice model AFTER saving payment ***
            $invoice->refresh(); 

            // Update invoice status based on the refreshed remaining balance
            if ($invoice->remaining_balance <= 0) {
                $invoice->status = 'paid'; // Use direct assignment
            } else {
                 // Only change to partially_paid if it was draft or sent
                if ($invoice->status === 'draft' || $invoice->status === 'sent') {
                     $invoice->status = 'partially_paid'; // Use direct assignment
                }
                // If already partially_paid or overdue, it stays that way
            }
            $invoice->save(); // Save the updated status

            DB::commit();

            // Redirect back - this will now load the refreshed invoice with correct status & payments
            return redirect()->route('invoices.show', $invoice)->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }
}
