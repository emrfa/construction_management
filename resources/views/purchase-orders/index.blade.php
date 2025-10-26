<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="mb-4">
                        <a href="{{ route('purchase-orders.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            + Create New PO
                        </a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO #</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total (Rp)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($purchaseOrders as $po)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ $po->po_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $po->supplier->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($po->order_date)->format('m/d/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ number_format($po->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @switch($po->status)
                                                @case('draft') bg-gray-200 text-gray-800 @break
                                                @case('ordered') bg-blue-200 text-blue-800 @break
                                                @case('partially_received') bg-yellow-200 text-yellow-800 @break
                                                @case('received') bg-green-200 text-green-800 @break
                                                @case('cancelled') bg-red-200 text-red-800 @break
                                            @endswitch">
                                            {{ ucfirst(str_replace('_', ' ', $po->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('purchase-orders.show', $po) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        No purchase orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>