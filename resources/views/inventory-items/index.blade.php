<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Inventory Item Master</h1>
                <p class="text-sm text-gray-500 mt-1">Manage and maintain all your inventory items.</p>
            </div>

            {{-- [MODIFIED] Added x-data to this outer div to make it available to the modal --}}
            <div class="flex items-center gap-3" x-data="{ selected: [], allIds: @json($items->pluck('id')) }">
                <a href="{{ route('inventory-items.importForm') }}"
                   class="px-4 py-2 bg-white border border-gray-300 rounded-xl shadow-sm font-medium text-sm text-gray-700 hover:bg-gray-50">
                    Import
                </a>

                <a href="#"
                   x-show="selected.length > 0"
                   x-bind:href="`{{ route('inventory-items.export') }}?${ selected.map(id => `selected_ids[]=${id}`).join('&') }`"
                   class="px-4 py-2 bg-emerald-600 rounded-xl text-white text-sm font-semibold shadow hover:bg-emerald-700">
                    Export (<span x-text="selected.length"></span>)
                </a>

                <a href="{{ route('inventory-items.export') }}"
                   x-show="selected.length === 0"
                   class="px-4 py-2 bg-emerald-600 rounded-xl text-white text-sm font-semibold shadow hover:bg-emerald-700">
                    Export All
                </a>

                <a href="{{ route('inventory-items.create') }}"
                   class="px-4 py-2 bg-indigo-600 rounded-xl text-white text-sm font-semibold shadow hover:bg-indigo-700 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Item
                </a>
            </div>
        </div>
    </x-slot>

    {{-- [MODIFIED] Moved x-data to the main div and added `itemToDeleteId` --}}
    <div class="py-10" x-data="selectionHandler">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-6">

            <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-2xl p-6 border border-gray-100">
                <form method="GET" action="{{ route('inventory-items.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- Item Name Filter --}}
                        <div>
                            <label for="select-name" class="text-sm font-medium text-gray-700">Item Name(s)</label>
                            <select name="names[]" id="select-name" multiple
                                    class="mt-1 w-full"
                                    placeholder="Filter by name...">
                                @foreach($allitems as $item)
                                    <option value="{{ $item->item_name }}" {{ in_array($item->item_name, request('names', [])) ? 'selected' : '' }}>
                                        {{ $item->item_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Category Filter --}}
                        <div>
                            <label for="select-category" class="text-sm font-medium text-gray-700">Category(s)</label>
                            <select name="categories[]" id="select-category" multiple
                                    class="mt-1 w-full"
                                    placeholder="Filter by category...">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ in_array($category->id, request('categories', [])) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm shadow hover:bg-indigo-700">
                                Apply
                            </button>
                            <a href="{{ route('inventory-items.index') }}"
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
                                <th class="p-4 w-4">
                                    <input type="checkbox" @click="toggleAll()" :checked="allSelected()"
                                           class="rounded border-gray-400 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Code</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">UOM</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Base Price</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($items as $item)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="p-4">
                                    <input type="checkbox" x-model="selected" value="{{ $item->id }}"
                                           class="rounded border-gray-400 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $item->item_code }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $item->item_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $item->itemCategory->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $item->uom }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">Rp {{ number_format($item->base_purchase_price, 0) }}</td>
                                <td class="px-6 py-4 text-sm font-bold {{ $item->quantity_on_hand <= $item->reorder_level ? 'text-red-600' : 'text-gray-700' }}">
                                    {{ $item->quantity_on_hand }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium flex gap-3">
                                    <a href="{{ route('inventory-items.edit', $item) }}" class="text-gray-500 hover:text-indigo-600" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    {{-- [MODIFIED] Removed form, added Alpine-powered button --}}
                                    <button type="button" 
                                            @click="itemToDeleteId = {{ $item->id }}; $dispatch('open-modal', 'confirm-item-deletion')"
                                            class="text-gray-500 hover:text-red-600" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                          <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-gray-500 text-sm">
                                    No items found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $items->links() }}
                </div>
            </div>

            {{-- [NEW] This is the one modal that handles all deletions --}}
            <x-modal name="confirm-item-deletion" focusable>
                {{-- 
                    The form's `action` is now dynamic.
                    It uses Alpine's `x-bind` to build the URL using the `itemToDeleteId` variable.
                --}}
                <form method="post" x-bind:action="`/inventory-items/${itemToDeleteId}`" class="p-6">
                    @csrf
                    @method('delete')

                    <h2 class="text-lg font-medium text-gray-900">
                        {{ __('Are you sure you want to delete this item?') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Once this item is deleted, all of its data will be permanently removed. This action cannot be undone.') }}
                    </p>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-danger-button class="ml-3">
                            {{ __('Delete Item') }}
                        </x-danger-button>
                    </div>
                </form>
            </x-modal>

        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('selectionHandler', () => ({
                selected: [],
                allIds: @json($items->pluck('id')),
                itemToDeleteId: null, // <-- [ADDED] This holds the ID for the modal
                
                toggleAll() {
                    if (this.allSelected()) {
                        this.selected = [];
                    } else {
                        this.selected = Array.from(this.allIds);
                    }
                },
                allSelected() {
                    return this.selected.length === this.allIds.length && this.allIds.length > 0;
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the multi-select for Categories
            new TomSelect('#select-category', {
                plugins: ['remove_button'],
                create: false,
                sortField: { field: "text", direction: "asc" }
            });
            
            // Initialize the multi-select for Item Names
            new TomSelect('#select-name', {
                plugins: ['remove_button'],
                create: false,
                sortField: { field: "text", direction: "asc" }
            });
        });
    </script>
    @endpush
</x-app-layout>