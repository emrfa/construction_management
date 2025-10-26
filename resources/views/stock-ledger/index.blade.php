<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Stock Ledger') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <p class="text-sm text-gray-600 mb-4">Showing all inventory movements, newest first.</p>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost (Rp)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source Ref</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($transactions as $tx)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $tx->item->item_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $tx->item->item_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold
                                        {{ $tx->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $tx->quantity > 0 ? '+' : '' }}{{ number_format($tx->quantity, 2, ',', '.') }}
                                        <span class="text-xs text-gray-500">{{ $tx->item->uom }}</span>
                                    </td>
                                     <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ number_format($tx->unit_cost, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{-- Extract class name like 'PurchaseOrder' or 'ProgressUpdate' --}}
                                        {{ class_basename($tx->sourceable_type) }}
                                    </td>
                                     <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @php
                                            $source = $tx->sourceable; // Get the related model (PO or ProgressUpdate)
                                            $link = '#'; // Default link
                                            $text = 'ID: ' . $tx->sourceable_id; // Default text

                                            if ($source instanceof \App\Models\PurchaseOrder) {
                                                $link = route('purchase-orders.show', $source);
                                                $text = $source->po_number;
                                            } elseif ($source instanceof \App\Models\ProgressUpdate) {
                                                // Link to the project dashboard page associated with the progress update
                                                $project = $source->quotationItem?->quotation?->project;
                                                if ($project) {
                                                    $link = route('projects.show', $project);
                                                    // Add task description or ID for context if desired
                                                    $text = $project->project_code . ' (Update)';
                                                } else {
                                                    $text = 'Progress Update ID: ' . $source->id;
                                                }
                                            }
                                        @endphp

                                        <a href="{{ $link }}" class="text-indigo-600 hover:text-indigo-900" title="Type: {{ class_basename($tx->sourceable_type) }}">
                                            {{ $text }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No stock transactions found yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>