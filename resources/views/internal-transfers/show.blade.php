<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="{{ route('internal-transfers.index') }}" class="text-indigo-600 hover:text-indigo-900">
                    &larr; Internal Transfers
                </a>
                <span class="text-gray-500">/</span>
                <span>{{ $internalTransfer->transfer_number }}</span>
            </h2>
            <div class="flex items-center space-x-2">
                @if ($internalTransfer->status == 'draft')
                    {{-- Ship Button --}}
                    <a href="{{ route('transfer-shipments.create', $internalTransfer) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Ship Items
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Transfer Details --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div>
                            <span class="block text-sm font-medium text-gray-500">Source Location</span>
                            <span class="text-lg font-semibold">{{ $internalTransfer->sourceLocation->name }}</span>
                            <div class="text-xs text-gray-500">{{ $internalTransfer->sourceLocation->code }}</div>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500">Destination Location</span>
                            <span class="text-lg font-semibold">{{ $internalTransfer->destinationLocation->name }}</span>
                            <div class="text-xs text-gray-500">{{ $internalTransfer->destinationLocation->code }}</div>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500">Status</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $internalTransfer->status == 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $internalTransfer->status == 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $internalTransfer->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            ">
                                {{ ucfirst($internalTransfer->status) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500">Created By</span>
                            <span>{{ $internalTransfer->createdBy->name }}</span>
                            <div class="text-xs text-gray-500">{{ $internalTransfer->created_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                    
                    @if($internalTransfer->materialRequest)
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-gray-500">Linked Material Request:</span>
                            <a href="{{ route('material-requests.show', $internalTransfer->materialRequest) }}" class="text-indigo-600 hover:underline font-medium ml-1">
                                {{ $internalTransfer->materialRequest->request_code }}
                            </a>
                        </div>
                    @endif

                    @if($internalTransfer->notes)
                        <div class="mb-6">
                            <span class="block text-sm font-medium text-gray-500">Notes</span>
                            <p class="text-gray-700 mt-1">{{ $internalTransfer->notes }}</p>
                        </div>
                    @endif

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Requested Items</h3>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Shipped</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($internalTransfer->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ $item->inventoryItem->item_code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->inventoryItem->item_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->quantity_requested, 2) }} {{ $item->inventoryItem->uom }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item->quantity_shipped, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium {{ ($item->quantity_requested - $item->quantity_shipped) > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                            {{ number_format($item->quantity_requested - $item->quantity_shipped, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Shipments List --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Shipments (Outbound)</h3>
                    @if($internalTransfer->shipments->count() > 0)
                        <div class="overflow-x-auto border rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipment #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($internalTransfer->shipments as $shipment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                                <a href="{{ route('transfer-shipments.show', $shipment) }}">{{ $shipment->shipment_number }}</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $shipment->shipped_date->format('d M Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $shipment->status == 'in_transit' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $shipment->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                <a href="{{ route('transfer-shipments.show', $shipment) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No shipments created yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
