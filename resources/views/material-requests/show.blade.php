<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="{{ route('projects.show', $materialRequest->project) }}" class="text-indigo-600 hover:text-indigo-900">
                    &larr; {{ $materialRequest->project->project_code }}
                </a>
                <span class="text-gray-500">/</span>
                <span>Material Request {{ $materialRequest->request_code }}</span>
            </h2>
            <div class="flex items-center space-x-2">
                @if ($materialRequest->status == 'pending_approval')
                    <form method="POST" action="{{ route('material-requests.updateStatus', $materialRequest) }}">
                        @csrf
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Reject
                        </button>
                    </form>
                    <form method="POST" action="{{ route('material-requests.updateStatus', $materialRequest) }}">
                        @csrf
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Approve Request
                        </button>
                    </form>
                @elseif ($materialRequest->status === 'approved' || $materialRequest->status === 'partially_fulfilled')
                    @php
                        // Check if there are any items that still need fulfilling
                        $needsFulfillment = $materialRequest->items->some(fn($item) => $item->quantity_requested > $item->quantity_fulfilled);
                    @endphp
                    @if($needsFulfillment)
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('material-requests.createPO', $materialRequest) }}">
                                @csrf
                                <button type="submit" class="bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    + Create Draft PO
                                </button>
                            </form>
                            <form method="POST" action="{{ route('material-requests.createTransfer', $materialRequest) }}">
                                @csrf
                                {{-- We use a link to the create page instead of a direct POST to allow user to edit details first --}}
                                <a href="{{ route('internal-transfers.create', ['material_request_id' => $materialRequest->id]) }}" class="inline-flex items-center justify-center bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm h-full">
                                    + Fulfill via Transfer
                                </a>
                            </form>
                        </div>
                    @else
                        <span class="px-4 py-2 bg-purple-200 text-purple-800 rounded-md font-semibold text-sm">
                            Request Fulfilled
                        </span>
                    @endif
                @elseif ($materialRequest->status == 'rejected')
                     <form method="POST" action="{{ route('material-requests.updateStatus', $materialRequest) }}">
                        @csrf
                        <input type="hidden" name="status" value="pending_approval"> {{-- Or 'draft' --}}
                        <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm mt-2">
                            Re-open Request
                        </button>
                    </form>
                @endif
                {{-- Add Edit/Cancel buttons if needed based on status --}}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b pb-4">
                        <div>
                            <strong class="text-gray-600 block text-sm">Project:</strong>
                            <span class="text-lg">{{ $materialRequest->project->quotation->project_name }}</span>
                            <span class="text-sm text-gray-500">({{ $materialRequest->project->project_code }})</span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">Request Code:</strong>
                            <span class="text-lg font-mono">{{ $materialRequest->request_code }}</span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">Status:</strong>
                            <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full
                                @switch($materialRequest->status)
                                    @case('draft') bg-gray-200 text-gray-800 @break
                                    @case('pending_approval') bg-yellow-200 text-yellow-800 @break
                                    @case('approved') bg-green-200 text-green-800 @break
                                    @case('rejected') bg-red-200 text-red-800 @break
                                    @case('partially_fulfilled') bg-blue-200 text-blue-800 @break
                                    @case('fulfilled') bg-purple-200 text-purple-800 @break
                                    @case('cancelled') bg-gray-400 text-gray-800 @break
                                @endswitch">
                                {{ ucfirst(str_replace('_', ' ', $materialRequest->status)) }}
                            </span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">Requested By:</strong>
                            <span>{{ $materialRequest->requester->name ?? 'N/A' }}</span>
                            <span class="text-xs text-gray-500"> on {{ $materialRequest->request_date ? \Carbon\Carbon::parse($materialRequest->request_date)->format('M d, Y') : '' }}</span>
                        </div>
                        <div>
                            <strong class="text-gray-600 block text-sm">Required Date:</strong>
                            <span>{{ $materialRequest->required_date ? \Carbon\Carbon::parse($materialRequest->required_date)->format('M d, Y') : '-' }}</span>
                        </div>
                         <div>
                            <strong class="text-gray-600 block text-sm">Approved By:</strong>
                            <span>{{ $materialRequest->approver->name ?? '-' }}</span>
                            {{-- Could add approval date later --}}
                        </div>
                         @if($materialRequest->notes)
                        <div class="md:col-span-3">
                            <strong class="text-gray-600 block text-sm">Notes:</strong>
                            <p class="mt-1 text-sm text-gray-700">{{ $materialRequest->notes }}</p>
                        </div>
                        @endif
                    </div>

                    <div>
                         <h4 class="font-semibold mb-2 text-gray-800">Requested Items</h4>
                         <table class="min-w-full divide-y divide-gray-200 text-sm border rounded">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">WBS Task</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Code</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Name</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Requested</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Fulfilled</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($materialRequest->items as $item)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-600">{{ $item->quotationItem->description ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap font-mono">{{ $item->inventoryItem->item_code ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $item->inventoryItem->item_name ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right">
                                            {{ number_format($item->quantity_requested, 2, ',', '.') }}
                                            <span class="text-xs text-gray-500">{{ $item->inventoryItem->uom ?? '' }}</span>
                                        </td>
                                         <td class="px-4 py-2 whitespace-nowrap text-right">{{ number_format($item->quantity_fulfilled, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 text-center text-gray-500">No items requested.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">
                    <h4 class="font-semibold mb-2 text-gray-800">Associated Purchase Orders</h4>
                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">PO #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($materialRequest->purchaseOrders as $po)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <a href="{{ route('purchase-orders.show', $po) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                {{ $po->po_number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $po->supplier?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
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
                                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('purchase-orders.show', $po) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-center text-gray-500">
                                            No Purchase Orders have been created from this request yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="font-semibold mb-2 text-gray-800">Associated Internal Transfers</h4>
                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Transfer #</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Destination</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($materialRequest->internalTransfers as $transfer)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <a href="{{ route('internal-transfers.show', $transfer) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                {{ $transfer->transfer_number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $transfer->sourceLocation->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{ $transfer->destinationLocation->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @switch($transfer->status)
                                                    @case('draft') bg-gray-200 text-gray-800 @break
                                                    @case('processing') bg-blue-200 text-blue-800 @break
                                                    @case('completed') bg-green-200 text-green-800 @break
                                                    @case('cancelled') bg-red-200 text-red-800 @break
                                                @endswitch">
                                                {{ ucfirst($transfer->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('internal-transfers.show', $transfer) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 text-center text-gray-500">
                                            No Internal Transfers have been created from this request yet.
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