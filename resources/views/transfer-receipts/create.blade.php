<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Receive Shipment') }} {{ $transferShipment->shipment_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('transfer-receipts.store', $transferShipment) }}" id="receipt-form">
                        @csrf
                        
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Source</span>
                                    <span class="font-semibold">{{ $transferShipment->sourceLocation->name }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500">Destination (Receiving At)</span>
                                    <span class="font-semibold">{{ $transferShipment->destinationLocation->name }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="received_date" :value="__('Received Date')" />
                                <x-text-input id="received_date" class="block mt-1 w-full" type="date" name="received_date" :value="old('received_date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('received_date')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="notes" :value="__('Notes')" />
                                <textarea id="notes" name="notes" rows="1" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Items to Receive</h3>
                            
                            <div class="overflow-x-auto border rounded-lg mb-6">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Shipped</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Already Received</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Receive Now</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($transferShipment->items as $index => $item)
                                            @php
                                                $remaining = $item->quantity_shipped - $item->quantity_received;
                                            @endphp
                                            @if($remaining > 0.001)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ $item->inventoryItem->item_name }}
                                                        <div class="text-xs text-gray-500">{{ $item->inventoryItem->item_code }}</div>
                                                        <input type="hidden" name="items[{{ $index }}][shipment_item_id]" value="{{ $item->id }}">
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">{{ number_format($item->quantity_shipped, 2) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">{{ number_format($item->quantity_received, 2) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">{{ number_format($remaining, 2) }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                                        <input type="number" step="0.01" name="items[{{ $index }}][quantity_received]" 
                                                               class="w-32 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm text-right" 
                                                               max="{{ $remaining }}" min="0" value="{{ $remaining }}">
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 gap-4">
                            <a href="{{ route('transfer-shipments.show', $transferShipment) }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow-sm">
                                Receive Items
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
