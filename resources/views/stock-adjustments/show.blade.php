<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('stock-adjustments.index') }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; Adjustment Log
            </a>
            <span class="text-gray-500">/</span>
            <span>Details for {{ $stockAdjustment->adjustment_no }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-b pb-6">
                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Adjustment No.</dt>
                                <dd class="mt-1 text-gray-900 font-mono font-semibold">{{ $stockAdjustment->adjustment_no }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Date</dt>
                                <dd class="mt-1 text-gray-900">{{ \Carbon\Carbon::parse($stockAdjustment->adjustment_date)->format('F d, Y') }}</dd>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Location</dt>
                                <dd class="mt-1 text-gray-900 font-semibold">{{ $stockAdjustment->location->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Adjusted By</dt>
                                <dd class="mt-1 text-gray-900">{{ $stockAdjustment->user->name ?? 'N/A' }}</dd>
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <dt class="text-sm font-medium text-gray-500">Reason / Notes</dt>
                            <dd class="mt-1 text-gray-700 bg-gray-50 border p-3 rounded-md">{{ $stockAdjustment->reason }}</dd>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-2 text-gray-800">Adjusted Items</h4>
                        <div class="overflow-x-auto border rounded">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">System Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Physical Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Adjustment</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Avg. Cost</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Financial Impact</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php $totalImpact = 0; @endphp
                                    @forelse ($stockAdjustment->items as $item)
                                        @php
                                            $impact = $item->adjustment_qty * $item->unit_cost;
                                            $totalImpact += $impact;
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap font-mono">{{ $item->item->item_code }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ $item->item->item_name }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right text-gray-600">{{ number_format($item->system_qty, 2) }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right font-medium">{{ number_format($item->physical_qty, 2) }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right font-bold {{ $item->adjustment_qty < 0 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $item->adjustment_qty > 0 ? '+' : '' }}{{ number_format($item->adjustment_qty, 2) }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right text-gray-600">{{ number_format($item->unit_cost, 0) }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right font-medium {{ $impact < 0 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ number_format($impact, 0) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-2 text-center text-gray-500">No items were adjusted.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="6" class="px-4 py-2 text-right font-bold text-gray-700">Total Financial Impact</td>
                                        <td class="px-4 py-2 text-right font-bold text-lg {{ $totalImpact < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($totalImpact, 0) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>