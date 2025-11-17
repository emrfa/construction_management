<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Goods Receiving</h1>
                <p class="text-sm text-gray-500 mt-1">Track all incoming material shipments.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('goods-receipts.create') }}" class="px-4 py-2 bg-indigo-600 rounded-xl text-white text-sm font-semibold shadow hover:bg-indigo-700 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Receive (Non-PO)
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-6">
            <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-2xl p-6 border border-gray-100">
                <form method="GET" action="{{ route('goods-receipts.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="text-sm font-medium text-gray-700">Search Receipts</label>
                            <x-text-input type="text" name="search" id="search" class="mt-1 w-full rounded-xl border-gray-300" placeholder="Search by receipt #, PO #, or supplier..." value="{{ request('search') }}"/>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm shadow hover:bg-indigo-700">Search</button>
                            <a href="{{ route('goods-receipts.index') }}" class="px-4 py-2 bg-white border rounded-xl text-sm text-gray-700 hover:bg-gray-50 shadow-sm">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Receipt #</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">PO Ref</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($receipts as $receipt)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">{{ $receipt->receipt_no }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $receipt->receipt_date->format('d-M-Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $receipt->purchaseOrder?->po_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $receipt->supplier?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $receipt->location?->code ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $receipt->status == 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($receipt->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                        @if($receipt->status == 'draft')
                                            <a href="{{ route('goods-receipts.edit', $receipt) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Receive</a>
                                        @else
                                            <a href="{{ route('goods-receipts.show', $receipt) }}" class="text-gray-500 hover:text-indigo-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="py-6 text-center text-gray-500 text-sm">No receipts found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50">{{ $receipts->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>