<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="{{ route('internal-transfers.show', $transferShipment->internalTransfer) }}" class="text-indigo-600 hover:text-indigo-900">
                    &larr; Transfer {{ $transferShipment->internalTransfer->transfer_number }}
                </a>
                <span class="text-gray-500">/</span>
                <span>Shipment {{ $transferShipment->shipment_number }}</span>
            </h2>
            <div class="flex items-center space-x-2">
                @if ($transferShipment->status == 'in_transit')
                    {{-- Receive Button --}}
                    <a href="{{ route('transfer-receipts.create', $transferShipment) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Receive Items
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Shipment Details --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div>
                            <span class="block text-sm font-medium text-gray-500">Source Location</span>
                            <span class="text-lg font-semibold">{{ $transferShipment->sourceLocation->name }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500">Destination Location</span>
                            <span class="text-lg font-semibold">{{ $transferShipment->destinationLocation->name }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500">Status</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $transferShipment->status == 'in_transit' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $transferShipment->status)) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500">Shipped Date</span>
                            <span>{{ $transferShipment->shipped_date->format('d M Y') }}</span>
                        </div>
                    </div>

                    @if($transferShipment->notes)
                        <div class="mb-6">
                            <span class="block text-sm font-medium text-gray-500">Notes</span>
                            <p class="text-gray-700 mt-1">{{ $transferShipment->notes }}</p>
                        </div>
                    @endif

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Shipped Items</h3>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Shipped Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Received Qty</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost (WAC)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($transferShipment->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ $item->inventoryItem->item_code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->inventoryItem->item_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->quantity_shipped, 2) }} {{ $item->inventoryItem->uom }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->quantity_received, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">{{ number_format($item->unit_cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Receipts List --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Receipts (Inbound)</h3>
                    @if($transferShipment->receipts->count() > 0)
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received By</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($transferShipment->receipts as $receipt)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $receipt->receipt_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $receipt->received_date->format('d M Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $receipt->receivedBy->name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No receipts created yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
