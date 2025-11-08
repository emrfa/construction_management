<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('goods-receipts.index') }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; All Receipts
            </a>
            <span class="text-gray-500">/</span>
            <span>Receipt {{ $goodsReceipt->receipt_no }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b pb-4">
                        <div>
                            <strong class="text-gray-600 block text-sm">Receipt #</strong>
                            <span class="text-lg font-mono">{{ $goodsReceipt->receipt_no }}</span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">Receipt Date</strong>
                            <span class="text-lg">{{ $goodsReceipt->receipt_date->format('F d, Y') }}</span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">Status</strong>
                            <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-200 text-green-800">
                                {{ ucfirst($goodsReceipt->status) }}
                            </span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">Supplier</strong>
                            <span>{{ $goodsReceipt->supplier?->name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">PO Reference</strong>
                            <span>
                                @if($goodsReceipt->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="text-indigo-600 hover:underline">
                                    {{ $goodsReceipt->purchaseOrder->po_number }}
                                </a>
                                @else
                                N/A
                                @endif
                            </span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">Project</strong>
                            <span>
                                @if($goodsReceipt->project)
                                <a href="{{ route('projects.show', $goodsReceipt->project) }}" class="text-indigo-600 hover:underline">
                                    {{ $goodsReceipt->project->project_code }}
                                </a>
                                @else
                                N/A (General Stock)
                                @endif
                            </span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">Received By</strong>
                            <span>{{ $goodsReceipt->receiver?->name ?? 'N/A' }}</span>
                        </div>
                        @if($goodsReceipt->notes)
                        <div class="md:col-span-3">
                            <strong class="text-gray-600 block text-sm">Notes</strong>
                            <p class="mt-1 text-sm text-gray-700">{{ $goodsReceipt->notes }}</p>
                        </div>
                        @endif
                    </div>

                    <div>
                        <h4 class="font-semibold mb-2 text-gray-800">Received Items</h4>
                        <table class="min-w-full divide-y divide-gray-200 text-sm border rounded">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty Received</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Cost (Rp)</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($goodsReceipt->items as $item)
                                    @if($item->quantity_received > 0) {{-- Only show items that were actually received on this doc --}}
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap font-mono">{{ $item->inventoryItem->item_code }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ $item->inventoryItem->item_name }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right">{{ number_format($item->quantity_received, 2) }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right">{{ number_format($item->unit_cost, 0) }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right font-semibold">{{ number_format($item->quantity_received * $item->unit_cost, 0) }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 text-center text-gray-500">No items on this receipt.</td>
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