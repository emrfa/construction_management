<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="{{ route('invoices.index') }}" class="text-indigo-600 hover:text-indigo-900">
                    &larr; All Invoices
                </a>
                <span class="text-gray-500">/</span>
                <span>Invoice {{ $invoice->invoice_no }}</span>
            </h2>
            <div class="flex items-center space-x-2">
                    @if ($invoice->status == 'draft')
                        <a href="{{ route('invoices.edit', $invoice) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Edit Invoice
                        </a>
                        <form method="POST" action="{{ route('invoices.updateStatus', $invoice) }}">
                            @csrf
                            <input type="hidden" name="status" value="sent">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Mark as Sent
                            </button>
                        </form>
                    @elseif ($invoice->status == 'sent' || $invoice->status == 'partially_paid' || $invoice->status == 'overdue')
                         <button type="button" onclick="document.getElementById('paymentModal').classList.remove('hidden')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Record Payment
                        </button>
                    @endif
            </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div>
                            <strong class="text-gray-600">Client:</strong>
                            <p class="text-lg">{{ $invoice->client->name }}</p>
                            <p class="text-sm">{{ $invoice->client->company_name }}</p>
                        </div>
                        <div>
                                <strong class="text-gray-600">Project:</strong>
                                <p class="text-lg">{{ $invoice->billing->project->quotation->project_name }}</p>
                                @if($invoice->billing->notes)
                                    <p class="text-xs text-gray-500 mt-1">
                                        <em>Billing For: {{ $invoice->billing->notes }}</em>
                                    </p>
                                @endif
                        </div>
    
                        <div>
                            <strong class="text-gray-600">Invoice #:</strong>
                            <p class="text-lg">{{ $invoice->invoice_no }}</p>
                            <p class="text-sm"><strong>Issued:</strong> {{ \Carbon\Carbon::parse($invoice->issued_date)->format('F j, Y') }}</p>
                            <p class="text-sm"><strong>Due:</strong> {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('F j, Y') : 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-6 border-t pt-4">
                        <div>
                            <strong class="text-gray-600">Status:</strong>
                            <p>
                                <span class="font-medium px-2 py-0.5 rounded text-sm
                                    @switch($invoice->status)
                                        @case('draft') bg-gray-200 text-gray-800 @break
                                        @case('sent') bg-blue-200 text-blue-800 @break
                                        @case('partially_paid') bg-yellow-200 text-yellow-800 @break
                                        @case('paid') bg-green-200 text-green-800 @break
                                        @case('overdue') bg-orange-200 text-orange-800 @break
                                        @case('cancelled') bg-red-200 text-red-800 @break
                                    @endswitch
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                </span>
                            </p>
                        </div>
                         <div>
                            <strong class="text-gray-600">Amount:</strong>
                            <p class="text-lg">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</p>
                            @if($invoice->tax_amount > 0)
                                <p class="text-sm text-gray-500">+ Tax: Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</p>
                            @endif
                        </div>
                        <div>
                            <strong class="text-gray-600">Total Due:</strong>
                            <p class="text-lg font-semibold">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-500">Paid: Rp {{ number_format($invoice->total_paid, 0, ',', '.') }}</p>
                            <p class="text-sm font-bold {{ $invoice->remaining_balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Balance: Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <h4 class="font-semibold mb-2">Payment History</h4>
                        @if($invoice->payments->count() > 0)
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                 <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (Rp)</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($payment->payment_date)->format('m/d/Y') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ number_format($payment->amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $payment->payment_method }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $payment->notes }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-sm text-gray-500">No payments recorded yet.</p>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="paymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center hidden">
        <div class="relative mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Record Payment for {{ $invoice->invoice_no }}</h3>
                 <p class="text-sm text-gray-600 mb-4">Amount Due: Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</p>

                <form action="{{ route('payments.store', $invoice) }}" method="POST">
                    @csrf
                    <div class="mt-2 px-7 py-3 space-y-4 text-left">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount Paid (Rp)</label>
                            <input type="number" step="0.01" name="amount" id="amount" value="{{ $invoice->remaining_balance }}" max="{{ $invoice->remaining_balance }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment Date</label>
                            <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <input type="text" name="payment_method" id="payment_method" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="e.g., Bank Transfer">
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 mr-2">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-700">
                            Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-app-layout>