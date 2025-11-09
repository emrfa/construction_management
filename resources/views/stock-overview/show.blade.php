<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('stock-overview.index') }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; All Locations
            </a>
            <span class="text-gray-500">/</span>
            <span>{{ $location->name }} ({{ $location->code }})</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Inventory Details for {{ $location->name }}</h3>
                    
                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">On Hand</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">On Order</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Forecasted Qty</th>
                                    {{-- <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Forecasted</th> --}}
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($reportData as $row)
                                    @php
                                        $onHand = $row['on_hand'];
                                        $onOrder = $row['on_order'];
                                        $available = $onHand + $onOrder;
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap font-mono">{{ $row['item']->item_code }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $row['item']->item_name }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $row['item']->uom }}</td>
                                        
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold {{ $onHand < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                            {{ number_format($onHand, 2) }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-blue-600">
                                            {{ $onOrder > 0 ? number_format($onOrder, 2) : '-' }}
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold {{ $available < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                            {{ number_format($available, 2) }}
                                        </td>
                                        {{-- <td class="px-3 py-2 whitespace-nowrap text-right text-gray-600">...</td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                                            No inventory found for this location.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>