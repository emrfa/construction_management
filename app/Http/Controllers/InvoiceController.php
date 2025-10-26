<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load the related client and billing info
        $invoices = Invoice::with(['client', 'billing.project.quotation'])->latest()->get();
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['client', 'billing.project.quotation', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
        return redirect()->route('invoices.show', $invoice)->with('error', 'Only draft invoices can be updated.');
    }

    $validated = $request->validate([
        'due_date' => 'nullable|date|after_or_equal:issued_date',
        // Add validation for other editable fields like tax if needed
    ]);

    // Recalculate total if tax is added/changed
    // $validated['total_amount'] = $invoice->amount + ($validated['tax_amount'] ?? 0);

    $invoice->update($validated);

    return redirect()->route('invoices.show', $invoice)
                     ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
            if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Only draft invoices can be edited.');
        }
        return view('invoices.edit', compact('invoice'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                // Define allowed statuses users can manually set
                Rule::in(['sent', 'cancelled', 'draft']), 
            ],
        ]);

        $newStatus = $validated['status'];

        // Add any specific logic checks here (e.g., cannot cancel if paid)
        if (($invoice->status === 'paid' || $invoice->status === 'partially_paid') && $newStatus === 'cancelled') {
            return back()->with('error', 'Cannot cancel an invoice that has received payments.');
        }

        $invoice->status = $newStatus;
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)
                        ->with('success', 'Invoice status updated successfully.');
    }

    
}
