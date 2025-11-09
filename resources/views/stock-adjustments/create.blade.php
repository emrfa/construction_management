<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Stock Adjustment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8"> {{-- Made page wider --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" x-data="adjustmentForm()">
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-md">
                            <strong class="font-bold">Whoops! Something went wrong.</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('stock-adjustments.store') }}">
                        @csrf
                        {{-- This input stores the final data for the controller --}}
                        <input type="hidden" name="items_json" :value="JSON.stringify(itemsToSubmit)">

                        {{-- HEADER SECTION --}}
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="adjustment_date" :value="__('Adjustment Date')" />
                                    <x-text-input id="adjustment_date" class="block mt-1 w-full" type="date" name="adjustment_date" :value="old('adjustment_date', date('Y-m-d'))" required />
                                </div>
                                <div>
                                    <x-input-label for="stock_location_id" :value="__('Stock Location')" />
                                    <select id="stock_location_id" name="stock_location_id" x-model.number="locationId" @change="onLocationChange()" class="block mt-1 w-full" x-init="new TomSelect($el, {create: false, placeholder: 'Select a location...'})" required>
                                        <option value="">Select a location...</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}" {{ old('stock_location_id') == $location->id ? 'selected' : '' }}>
                                                {{ $location->name }} ({{ $location->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <x-input-label for="reason" :value="__('Reason for Adjustment')" />
                                <x-text-input id="reason" class="block mt-1 w-full" type="text" name="reason" :value="old('reason')" required placeholder="e.g., Monthly stock count, Write-off for damaged items, etc." />
                            </div>
                        </div>

                        <hr class="my-6">

                        {{-- CONTROLS SECTION --}}
                        <h3 class="text-lg font-semibold mb-2">Items to Adjust</h3>
                        <div x-show="locationId" class="flex items-center space-x-4 p-3 bg-gray-50 border rounded-md mb-4" x-transition>
                            <button type="button" @click="loadAllItems()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Load All Items from Location
                            </button>
                            <label class="inline-flex items-center">
                                <input x-model="includeZeroStock" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                <span class="ml-2 text-sm text-gray-600">Include items with 0 stock?</span>
                            </label>
                            <span x-show="loading" class="text-sm text-gray-500">
                                <svg class="animate-spin h-5 w-5 text-blue-600 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading...
                            </span>
                        </div>
                        <p x-show="!locationId" class="text-sm text-red-600 mt-2">Please select a location to load items.</p>

                        {{-- NEW ELEGANT TABLE DESIGN --}}
                        <div class="overflow-x-auto border rounded-md mt-4" x-show="items.length > 0">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">System Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase" style="width: 150px;">New Physical Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Adj. Qty</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase" style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(item, index) in items" :key="item.item_id">
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="font-medium text-gray-900" x-text="item.item_name"></div>
                                                <div class="text-xs text-gray-500" x-text="`${item.item_code} (${item.uom})`"></div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right font-bold text-blue-600" x-text="item.system_qty"></td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                                <x-text-input type="number" step="0.01" min="0" 
                                                              x-model.number="item.physical_qty"
                                                              class="block w-full text-right text-sm" />
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right font-bold"
                                                x-text="adjustmentQty(item)"
                                                :class="{ 'text-green-600': adjustmentQty(item) > 0, 'text-red-600': adjustmentQty(item) < 0, 'text-gray-500': adjustmentQty(item) == 0 }">
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700" title="Remove row">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- END TABLE DESIGN --}}

                        <div class="flex items-center justify-end mt-6 border-t pt-4">
                            <a href="{{ route('stock-adjustments.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Post Adjustment') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adjustmentForm', () => ({
            locationId: '{{ old('stock_location_id', '') }}',
            items: [], // This will hold our list of items to adjust
            stockDataCache: {}, // Caches stock levels for the selected location
            includeZeroStock: false,
            loading: false,

            init() {
                const oldItems = JSON.parse(@json(old('items_json', '[]')));
                if (oldItems.length > 0 && this.locationId) {
                    this.fetchStockData().then(() => {
                        oldItems.forEach(oldItem => {
                            const stockInfo = this.stockDataCache[oldItem.item_id] || {};
                            this.items.push({
                                item_id: oldItem.item_id,
                                item_name: stockInfo.item_name || 'Unknown Item',
                                item_code: stockInfo.item_code || 'N/A',
                                uom: stockInfo.uom || 'N/A',
                                system_qty: stockInfo.qty ?? 0,
                                physical_qty: oldItem.physical_qty
                            });
                        });
                    });
                } else if (this.locationId) {
                    this.fetchStockData();
                }
            },
            
            onLocationChange() {
                this.items = [];
                this.fetchStockData();
            },

            async fetchStockData() {
                if (!this.locationId) {
                    this.stockDataCache = {};
                    return;
                }
                this.loading = true;
                
                try {
                    // We always fetch all items for the cache
                    const response = await fetch(`/web-api/locations/${this.locationId}/stock?include_zero=true`);
                    if (!response.ok) throw new Error('Failed to fetch stock');
                    const data = await response.json();
                    
                    // CORRECTED: The API now returns a clean array
                    this.stockDataCache = data.reduce((acc, item) => {
                        acc[item.inventory_item_id] = {
                            qty: parseFloat(item.on_hand),
                            item_code: item.item_code,
                            item_name: item.item_name,
                            uom: item.uom
                        };
                        return acc;
                    }, {});

                } catch (error) {
                    console.error('Error fetching stock data:', error);
                    alert('Could not load stock data for this location.');
                } finally {
                    this.loading = false;
                }
            },

            loadAllItems() {
                this.items = []; // Clear list before loading
                Object.entries(this.stockDataCache).forEach(([id, data]) => {
                    
                    if (data.qty === 0 && !this.includeZeroStock) {
                        return; // Skip this item
                    }
                    
                    this.items.push({
                        item_id: id,
                        item_name: data.item_name,
                        item_code: data.item_code,
                        uom: data.uom,
                        system_qty: data.qty,
                        physical_qty: data.qty // Default physical to system
                    });
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },
            
            adjustmentQty(item) {
                let physical = parseFloat(item.physical_qty) || 0;
                let system = parseFloat(item.system_qty) || 0;
                let adj = physical - system;
                // Check if it's effectively zero to avoid -0.00
                if (Math.abs(adj) < 0.001) adj = 0; 
                return adj.toFixed(2);
            },
            
            // This filters the items for the controller.
            // Only submit items that have changed.
            get itemsToSubmit() {
                return this.items.filter(item => {
                    let physical = parseFloat(item.physical_qty) || 0;
                    let system = parseFloat(item.system_qty) || 0;
                    // Only include items where the quantity is different
                    return Math.abs(physical - system) >= 0.001; 
                }).map(item => {
                    // Send only the data the controller needs
                    return {
                        item_id: item.item_id,
                        physical_qty: parseFloat(item.physical_qty) || 0
                    };
                });
            }
        }));
    });
    </script>
    @endpush
</x-app-layout>