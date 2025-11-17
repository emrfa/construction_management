<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Supplier Management</h1>
                <p class="text-sm text-gray-500 mt-1">Manage all vendors and suppliers.</p>
            </div>
            <div class="flex items-center gap-3" x-data="supplierPageHandler">
                <a href="{{ route('suppliers.create') }}"
                   class="px-4 py-2 bg-indigo-600 rounded-xl text-white text-sm font-semibold shadow hover:bg-indigo-700 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Supplier
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="supplierPageHandler">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-6">

            <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-2xl p-6 border border-gray-100">
                <form method="GET" action="{{ route('suppliers.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="text-sm font-medium text-gray-700">Search Suppliers</label>
                            <x-text-input type="text" name="search" id="search"
                                          class="mt-1 w-full rounded-xl border-gray-300"
                                          placeholder="Search by name, contact, or email..."
                                          value="{{ request('search') }}"/>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm shadow hover:bg-indigo-700">
                                Search
                            </button>
                            <a href="{{ route('suppliers.index') }}"
                               class="px-4 py-2 bg-white border rounded-xl text-sm text-gray-700 hover:bg-gray-50 shadow-sm">
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
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact Person</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($suppliers as $supplier)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $supplier->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $supplier->contact_person }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $supplier->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $supplier->phone }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-3">
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="text-gray-500 hover:text-indigo-600" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <button type="button" 
                                                @click="supplierToDeleteId = {{ $supplier->id }}; $dispatch('open-modal', 'confirm-supplier-deletion')"
                                                class="text-gray-500 hover:text-red-600" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-500 text-sm">
                                        No suppliers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $suppliers->appends(request()->query())->links() }}
                </div>
            </div>

            <x-modal name="confirm-supplier-deletion" focusable>
                <form method="post" x-bind:action="`/suppliers/${supplierToDeleteId}`" class="p-6">
                    @csrf
                    @method('delete')
                    <h2 class="text-lg font-medium text-gray-900">{{ __('Are you sure you want to delete this supplier?') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ __('This action cannot be undone.') }}</p>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                        <x-danger-button class="ml-3">{{ __('Delete Supplier') }}</x-danger-button>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('supplierPageHandler', () => ({
                supplierToDeleteId: null,
            }));
        });
    </script>
    @endpush
</x-app-layout>