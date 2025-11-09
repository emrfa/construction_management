<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Stock Balance Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Stock on Hand by Location</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        This report shows the current stock for all items, grouped by location.
                        "Main Warehouse (General Stock)" is inventory not assigned to any specific project.
                    </p>

                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Location / Project</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">On Hand Qty</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($balances as $balance)
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap font-mono">{{ $balance->item->item_code }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $balance->item->item_name }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $balance->item->uom }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap font-medium">
                                            {{-- Use the new stockLocation relationship --}}
                                            @if ($balance->stockLocation)
                                                <span class="{{ $balance->stockLocation->type == 'warehouse' ? 'text-green-700' : 'text-gray-900' }}">
                                                    {{ $balance->stockLocation->name }}
                                                </span>
                                                @if($balance->stockLocation->type == 'site' && $balance->stockLocation->project)
                                                    <span class="text-gray-500">({{ $balance->stockLocation->project->project_code }})</span>
                                                @else
                                                    <span class="text-gray-500">({{ $balance->stockLocation->code }})</span>
                                                @endif
                                            @else
                                                <span class="text-red-600">!! No Location !!</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold {{ $balance->on_hand < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                            {{ number_format($balance->on_hand, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">
                                            No stock balances found.
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