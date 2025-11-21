<x-app-layout>
    {{-- 
      STRATEGY: 
      1. We create a global x-data on a wrapper to ensure Header and Body share the selection state.
      2. We use 'slate' colors for a more premium feel than standard 'gray'.
    --}}
    <div x-data="selectionHandler">
        
        <x-slot name="header">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Inventory Master</h1>
                    <p class="text-sm text-slate-500 mt-1">Centralized control for all stock items.</p>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Import: Minimalist Outline --}}
                    <a href="{{ route('inventory-items.importForm') }}"
                       class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors shadow-sm">
                        Import
                    </a>

                    {{-- Export (Conditional): Animated Transition --}}
                    <div x-show="selected.length > 0" x-transition.opacity.duration.200ms style="display: none;">
                        <a href="#"
                           x-bind:href="`{{ route('inventory-items.export') }}?${ selected.map(id => `selected_ids[]=${id}`).join('&') }`"
                           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-md shadow-emerald-200 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export (<span x-text="selected.length"></span>)
                        </a>
                    </div>

                    <div x-show="selected.length === 0" x-transition.opacity.duration.200ms>
                        <a href="{{ route('inventory-items.export') }}"
                           class="px-4 py-2 bg-white border border-slate-200 text-slate-600 hover:border-emerald-500 hover:text-emerald-600 rounded-lg text-sm font-medium shadow-sm transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export All
                        </a>
                    </div>

                    {{-- Add Item: Primary Call to Action --}}
                    <a href="{{ route('inventory-items.create') }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-md shadow-indigo-200 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Item
                    </a>
                </div>
            </div>
        </x-slot>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                {{-- 
                    FILTER SECTION 
                    Design: Floating "Control Panel" style. 
                --}}
                <div class="bg-white rounded-xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] border border-slate-100 p-1">
                    <form method="GET" action="{{ route('inventory-items.index') }}" class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                            
                            {{-- Name Filter --}}
                            <div class="md:col-span-5 space-y-1.5">
                                <label for="select-name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider ml-1">Search Items</label>
                                <div class="relative">
                                    <select name="names[]" id="select-name" multiple placeholder="Select items..." autocomplete="off">
                                        @foreach($allitems as $item)
                                            <option value="{{ $item->item_name }}" {{ in_array($item->item_name, request('names', [])) ? 'selected' : '' }}>
                                                {{ $item->item_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Category Filter --}}
                            <div class="md:col-span-4 space-y-1.5">
                                <label for="select-category" class="text-xs font-semibold text-slate-500 uppercase tracking-wider ml-1">Category</label>
                                <select name="categories[]" id="select-category" multiple placeholder="Select categories..." autocomplete="off">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ in_array($category->id, request('categories', [])) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Actions --}}
                            <div class="md:col-span-3 flex gap-2">
                                <button type="submit" class="flex-1 px-4 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition shadow-sm">
                                    Filter Data
                                </button>
                                <a href="{{ route('inventory-items.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 hover:text-slate-900 transition shadow-sm">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- 
                    TABLE SECTION 
                    Design: "Card" container with refined table styling.
                --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th scope="col" class="p-4 w-4 relative">
                                        <input type="checkbox" @click="toggleAll()" :checked="allSelected()"
                                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 transition cursor-pointer">
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Code</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Item Name</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Stock Level</th>
                                    <th scope="col" class="relative px-6 py-3.5">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @forelse ($items as $item)
                                <tr class="hover:bg-indigo-50/30 transition-colors duration-150 group">
                                    <td class="p-4">
                                        <input type="checkbox" x-model="selected" value="{{ $item->id }}"
                                               class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                    </td>
                                    {{-- Item Code: Monospace for technical feel --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-500">
                                        {{ $item->item_code }}
                                    </td>
                                    {{-- Name: Stronger emphasis --}}
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900">
                                        {{ $item->item_name }}
                                        <div class="text-xs font-normal text-slate-400 mt-0.5">UOM: {{ $item->uom }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $item->itemCategory->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    {{-- Price: Aligned neatly, standard currency look --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-700">
                                        Rp {{ number_format($item->base_purchase_price, 0) }}
                                    </td>
                                    {{-- Stock: Badge Logic --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $isLow = $item->quantity_on_hand <= $item->reorder_level;
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border {{ $isLow ? 'bg-red-50 text-red-700 border-red-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $isLow ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                            {{ $item->quantity_on_hand }}
                                        </span>
                                    </td>
                                    {{-- Actions: Subtle until hover --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-3 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('inventory-items.edit', $item) }}" class="text-slate-400 hover:text-indigo-600 transition-colors p-1 rounded-md hover:bg-indigo-50" title="Edit Item">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                </svg>
                                            </a>
                                            <button type="button" 
                                                    @click="itemToDeleteId = {{ $item->id }}; $dispatch('open-modal', 'confirm-item-deletion')"
                                                    class="text-slate-400 hover:text-red-600 transition-colors p-1 rounded-md hover:bg-red-50" title="Delete Item">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                            <svg class="w-12 h-12 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                            <span class="text-base font-medium text-slate-900">No items found</span>
                                            <p class="text-sm mt-1">Try adjusting your filters or create a new item.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination with distinct top border --}}
                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                        {{ $items->links() }}
                    </div>
                </div>

                {{-- Modal Logic remains largely the same but cleaner button styles --}}
                <x-modal name="confirm-item-deletion" focusable>
                    <form method="post" x-bind:action="`/inventory-items/${itemToDeleteId}`" class="p-6">
                        @csrf
                        @method('delete')

                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h2 class="text-lg font-semibold text-slate-900">
                                {{ __('Delete Inventory Item?') }}
                            </h2>
                        </div>

                        <p class="text-sm text-slate-500 ml-13 mb-6">
                            {{ __('This action cannot be undone. All data associated with this item (including stock history) will be permanently removed.') }}
                        </p>

                        <div class="mt-6 flex justify-end gap-3">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                {{ __('Cancel') }}
                            </x-secondary-button>

                            <x-danger-button>
                                {{ __('Delete Item') }}
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('selectionHandler', () => ({
                selected: [],
                allIds: @json($items->pluck('id')),
                itemToDeleteId: null, 
                
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

        // TomSelect Configuration (Styled)
        document.addEventListener('DOMContentLoaded', function() {
            const config = {
                plugins: ['remove_button', 'dropdown_input'],
                create: false,
                sortField: { field: "text", direction: "asc" },
                // Custom render to match Tailwind classes could go here, 
                // but standard TomSelect CSS usually blends okay if variables are tweaked.
            };
            new TomSelect('#select-category', config);
            new TomSelect('#select-name', config);
        });
    </script>
    @endpush
</x-app-layout>