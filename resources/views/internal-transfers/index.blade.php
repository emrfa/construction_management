<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Internal Transfers</h1>
                <p class="text-sm text-gray-500 mt-1">Manage stock movements between locations.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('internal-transfers.create') }}" class="px-4 py-2 bg-indigo-600 rounded-xl text-white text-sm font-semibold shadow hover:bg-indigo-700 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Create Transfer
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-6">
            <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-2xl p-6 border border-gray-100">
                <form method="GET" action="{{ route('internal-transfers.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="text-sm font-medium text-gray-700">Search Transfers</label>
                            <x-text-input type="text" name="search" id="search" class="mt-1 w-full rounded-xl border-gray-300" placeholder="Search by transfer #..." value="{{ request('search') }}"/>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm shadow hover:bg-indigo-700">Search</button>
                            <a href="{{ route('internal-transfers.index') }}" class="px-4 py-2 bg-white border rounded-xl text-sm text-gray-700 hover:bg-gray-50 shadow-sm">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Transfer #</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Source</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Destination</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created By</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($transfers as $transfer)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="{{ route('internal-transfers.show', $transfer) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">{{ $transfer->transfer_number }}</a>
                                        @if($transfer->materialRequest)
                                            <div class="text-xs text-gray-500">Ref: {{ $transfer->materialRequest->request_code }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $transfer->sourceLocation->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $transfer->destinationLocation->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $transfer->status == 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $transfer->status == 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $transfer->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        ">
                                            {{ ucfirst($transfer->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $transfer->createdBy->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                        <a href="{{ route('internal-transfers.show', $transfer) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-6 text-center text-gray-500 text-sm">No transfers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50">{{ $transfers->appends(request()->query())->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
