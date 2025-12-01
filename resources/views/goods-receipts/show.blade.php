<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Goods Receipts', 'url' => route('goods-receipts.index')],
            ['label' => $goodsReceipt->receipt_no, 'url' => '']
        ]" />
    </x-slot>

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

                    @if($goodsReceipt->backOrderReceipt)
                        <div class="p-4 bg-yellow-100 border border-yellow-300 text-yellow-800 rounded-md">
                            <p class="font-medium">
                                This was a partial receipt. A new back-order draft has been created:
                                <a href="{{ route('goods-receipts.edit', $goodsReceipt->backOrderReceipt) }}" class="font-bold underline text-yellow-900 hover:text-yellow-700">
                                    Click here to receive items on {{ $goodsReceipt->backOrderReceipt->receipt_no }}.
                                </a>
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 border-b pb-6">
                        
                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">PO Reference</dt>
                                <dd class="mt-1 text-gray-900">
                                    @if($goodsReceipt->purchaseOrder)
                                    <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}" class="text-indigo-600 hover:underline font-semibold">
                                        {{ $goodsReceipt->purchaseOrder->po_number }}
                                    </a>
                                    @else
                                    <span class="text-gray-700">N/A (Non-PO Receipt)</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Supplier</dt>
                                <dd class="mt-1 text-gray-900">{{ $goodsReceipt->supplier?->name ?? 'N/A' }}</dd>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Project</dt>
                                <dd class="mt-1 text-gray-900">
                                    @if($goodsReceipt->project)
                                    <a href="{{ route('projects.show', $goodsReceipt->project) }}" class="text-indigo-600 hover:underline font-semibold">
                                        {{ $goodsReceipt->project->project_code }}
                                    </a>
                                    @else
                                    <span class="text-gray-700">N/A (General Stock)</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Received By</dt>
                                <dd class="mt-1 text-gray-900">{{ $goodsReceipt->receiver?->name ?? 'N/A' }}</dd>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Receipt No.</dt>
                                <dd class="mt-1 text-gray-900 font-mono font-semibold">{{ $goodsReceipt->receipt_no }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Receipt Date</dt>
                                <dd class="mt-1 text-gray-900">{{ $goodsReceipt->receipt_date->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-200 text-green-800">
                                        {{ ucfirst($goodsReceipt->status) }}
                                    </span>
                                </dd>
                            </div>
                        </div>
                    </div>

                    @if($goodsReceipt->notes)
                    <div>
                        <h4 class="font-semibold text-gray-800">Notes</h4>
                        <p class="mt-1 text-sm text-gray-700">{{ $goodsReceipt->notes }}</p>
                    </div>
                    @endif

                    <div>
                        <h4 class="font-semibold mb-2 text-gray-800">Received Items</h4>
                        <div class="overflow-x-auto border rounded">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantity Received</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($goodsReceipt->items as $item)
                                        @if($item->quantity_received > 0)
                                            <tr>
                                                <td class="px-4 py-2 whitespace-nowrap font-mono">{{ $item->inventoryItem->item_code }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap">{{ $item->inventoryItem->item_name }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap">{{ $item->inventoryItem->uom }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-right font-semibold">{{ number_format($item->quantity_received, 2) }}</td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-2 text-center text-gray-500">No items were received on this document.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>