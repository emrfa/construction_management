<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Quotations (RAB)</h1>
                <p class="text-sm text-gray-500 mt-1">Manage project cost estimates and proposals.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('quotations.create') }}"
                   class="px-4 py-2 bg-indigo-600 rounded-xl text-white text-sm font-semibold shadow hover:bg-indigo-700 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Quotation
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="pageHandler">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-6">
            
            <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-2xl p-6 border border-gray-100">
                <form method="GET" action="{{ route('quotations.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        <div class="md:col-span-4 lg:col-span-1">
                            <label for="search" class="text-sm font-medium text-gray-700">Search</label>
                            <x-text-input type="text" name="search" id="search"
                                          class="mt-1 w-full rounded-xl border-gray-300"
                                          placeholder="Code, Project, or Client..."
                                          value="{{ request('search') }}"/>
                        </div>

                        <div>
                            <label for="status" class="text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ Str::title($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <div class="w-1/2">
                                <label for="date_from" class="text-sm font-medium text-gray-700">From</label>
                                <x-text-input type="date" name="date_from" id="date_from"
                                              class="mt-1 w-full rounded-xl border-gray-300"
                                              value="{{ request('date_from') }}"/>
                            </div>
                            <div class="w-1/2">
                                <label for="date_to" class="text-sm font-medium text-gray-700">To</label>
                                <x-text-input type="date" name="date_to" id="date_to"
                                              class="mt-1 w-full rounded-xl border-gray-300"
                                              value="{{ request('date_to') }}"/>
                            </div>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm shadow hover:bg-indigo-700 w-full md:w-auto">
                                Filter
                            </button>
                            <a href="{{ route('quotations.index') }}"
                               class="px-4 py-2 bg-white border rounded-xl text-sm text-gray-700 hover:bg-gray-50 shadow-sm w-full md:w-auto text-center">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Quotation #</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Project Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total (Rp)</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($quotations as $quotation)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="hover:underline">
                                            {{ $quotation->quotation_no }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ $quotation->project_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $quotation->client->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $quotation->date->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($quotation->total_estimate, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full
                                            @switch($quotation->status)
                                                @case('draft') bg-gray-100 text-gray-600 border border-gray-200 @break
                                                @case('sent') bg-blue-50 text-blue-600 border border-blue-100 @break
                                                @case('approved') bg-emerald-50 text-emerald-600 border border-emerald-100 @break
                                                @case('rejected') bg-red-50 text-red-600 border border-red-100 @break
                                            @endswitch">
                                            {{ ucfirst($quotation->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex justify-end gap-3">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="text-gray-400 hover:text-indigo-600 transition" title="View Details">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        @if ($quotation->status == 'draft')
                                            <a href="{{ route('quotations.edit', $quotation) }}" class="text-gray-400 hover:text-blue-600 transition" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <button type="button" 
                                                    @click="itemToDeleteId = {{ $quotation->id }}; $dispatch('open-modal', 'confirm-quotation-deletion')"
                                                    class="text-gray-400 hover:text-red-600 transition" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-gray-500 text-sm">
                                        No quotations found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $quotations->appends(request()->query())->links() }}
                </div>
            </div>

            <x-modal name="confirm-quotation-deletion" focusable>
                <form method="post" x-bind:action="`/quotations/${itemToDeleteId}`" class="p-6">
                    @csrf
                    @method('delete')

                    <h2 class="text-lg font-medium text-gray-900">
                        {{ __('Delete Quotation?') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Are you sure you want to delete this draft quotation? This action cannot be undone.') }}
                    </p>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-danger-button class="ml-3">
                            {{ __('Delete') }}
                        </x-danger-button>
                    </div>
                </form>
            </x-modal>

        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pageHandler', () => ({
                itemToDeleteId: null,
            }));
        });
    </script>
    @endpush
</x-app-layout>