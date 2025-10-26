<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; PO {{ $purchaseOrder->po_number }}
            </a>
            <span class="text-gray-500">/</span>
            <span>Receive Items</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">
                        @csrf

                        <p class="mb-4 text-sm text-gray-600">Enter the quantity received for each item. Leave blank or 0 if none were received in this shipment.</p>

                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Ordered</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Already Received</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Receiving Now</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($purchaseOrder->items as $index => $item)
                                @php
                                    // Calculate max quantity that can be received now
                                    $maxReceivable = $item->quantity_ordered - $item->quantity_received;
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item->inventoryItem->item_code }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item->inventoryItem->item_name }} ({{ $item->inventoryItem->uom }})</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right">{{ number_format($item->quantity_ordered, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right">{{ number_format($item->quantity_received, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right">
                                        <input type="hidden" name="items[{{ $index }}][po_item_id]" value="{{ $item->id }}">
                                        <input type="number" step="0.01" min="0" max="{{ $maxReceivable }}"
                                               name="items[{{ $index }}][quantity_received_now]"
                                               class="w-24 text-right border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                               value="0">
                                        <span class="text-xs text-gray-500 ml-1">Max: {{ number_format($maxReceivable, 2, ',', '.') }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ml-4">
                                {{ __('Confirm Received Items') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>