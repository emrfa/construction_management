<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="{{ route('purchase-orders.index') }}" class="text-indigo-600 hover:text-indigo-900">
                    &larr; All Purchase Orders
                </a>
                <span class="text-gray-500">/</span>
                <span>PO {{ $purchaseOrder->po_number }}</span>
            </h2>
            <div class="flex items-center space-x-2">
                @if ($purchaseOrder->status == 'draft')
                    <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm">Edit</a>

                    <form method="POST" action="{{ route('purchase-orders.updateStatus', $purchaseOrder) }}">
                        @csrf
                        <input type="hidden" name="status" value="ordered">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Mark as Ordered
                        </button>
                    </form>
                @elseif ($purchaseOrder->status == 'ordered' || $purchaseOrder->status == 'partially_received')
                    <a href="{{ route('purchase-orders.receiveForm', $purchaseOrder) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Receive Items
                    </a>
                @endif
                </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div>
                            <strong class="text-gray-600">Supplier:</strong>
                            <p class="text-lg">{{ $purchaseOrder->supplier->name }}</p>
                            <p class="text-sm">{{ $purchaseOrder->supplier->contact_person }}</p>
                            <p class="text-sm">{{ $purchaseOrder->supplier->phone }}</p>
                        </div>
                        <div>
                            <strong class="text-gray-600">Dates:</strong>
                            <p class="text-sm"><strong>Order Date:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('F j, Y') }}</p>
                            <p class="text-sm"><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date ? \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('F j, Y') : 'N/A' }}</p>
                        </div>
                        <div>
                            <strong class="text-gray-600">PO #:</strong>
                            <p class="text-lg">{{ $purchaseOrder->po_number }}</p>
                            <strong class="text-gray-600">Status:</strong>
                            <p>
                                <span class="font-medium px-2 py-0.5 rounded text-sm
                                    @switch($purchaseOrder->status)
                                        @case('draft') bg-gray-200 text-gray-800 @break
                                        @case('ordered') bg-blue-200 text-blue-800 @break
                                        @case('partially_received') bg-yellow-200 text-yellow-800 @break
                                        @case('received') bg-green-200 text-green-800 @break
                                        @case('cancelled') bg-red-200 text-red-800 @break
                                    @endswitch
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $purchaseOrder->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h3 class="text-lg font-semibold mb-2">Items Ordered</h3>
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UoM</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Ordered</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Cost (Rp)</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item->inventoryItem->item_code }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item->inventoryItem->item_name }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">{{ $item->inventoryItem->uom }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right">{{ number_format($item->quantity_ordered, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right">{{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 whitespace-nowrap text-right font-semibold">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="px-4 py-2 text-right font-bold uppercase text-gray-600">Total Amount:</td>
                                    <td class="px-4 py-2 text-right font-bold text-lg">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>