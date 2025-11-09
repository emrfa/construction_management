<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Material Usage Log') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <form method="GET" action="{{ route('material-usage.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="project_id" class="text-sm font-medium text-gray-700">Filter by Project</label>
                                <select name="project_id" id="project_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" x-init="new TomSelect($el, {create: false})">
                                    <option value="">All Projects</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->project_code }} - {{ $project->quotation->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end space-x-2">
                                <x-primary-button type="submit">
                                    Filter
                                </x-primary-button>
                                <a href="{{ route('material-usage.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto border rounded-md">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">WBS Task</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Used</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty Used</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                @forelse ($usages as $usage)
                                    @php
                                        // Find the matching stock transaction from the progress update
                                        $transaction = $usage->progressUpdate
                                                            ->stockTransactions
                                                            ->firstWhere('inventory_item_id', $usage->inventory_item_id);
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $usage->progressUpdate->date->format('Y-m-d') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $usage->progressUpdate->quotationItem->quotation->project->project_code ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                            {{ $usage->progressUpdate->quotationItem->description ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                            {{ $usage->inventoryItem->item_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-xs">
                                            {{ $transaction?->stockLocation?->code ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-red-600">
                                            -{{ number_format($usage->quantity_used, 2) }}
                                            <span class="text-xs text-gray-500">{{ $usage->inventoryItem->uom }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No material usage records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $usages->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>