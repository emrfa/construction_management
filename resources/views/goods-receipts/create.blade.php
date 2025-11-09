<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('goods-receipts.index') }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; Receipts
            </a>
            <span class="text-gray-500">/</span>
            <span>Create Non-PO Receipt</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div x-data="grnForm()">
                        <form method="POST" action="{{ route('goods-receipts.store') }}">
                            @csrf
                            <input type="hidden" name="items_json" :value="JSON.stringify(items.map(item => ({ inventory_item_id: item.inventory_item_id, quantity_received: item.quantity_received, unit_cost: item.unit_cost })))">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="receipt_date" :value="__('Receipt Date')" />
                                    <x-text-input id="receipt_date" class="block mt-1 w-full" type="date" name="receipt_date" :value="old('receipt_date', date('Y-m-d'))" required />
                                </div>
                                <div>
                                    <x-input-label for="supplier_id" :value="__('Supplier (Optional)')" />
                                    <select id="supplier_id" name="supplier_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" x-init="initializeTomSelect($el)">
                                        <option value="">Select a supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                {{-- NEW: Stock Location Dropdown --}}
                                <div>
                                    <x-input-label for="stock_location_id" :value="__('Receive To Location')" />
                                    <select id="stock_location_id" name="stock_location_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" x-init="initializeTomSelect($el)" required>
                                        <option value="">Select a location...</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }} ({{ $location->code }})</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('stock_location_id')" class="mt-2" />
                                </div>
                                
                                <div class="md:col-span-3">
                                    <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                    <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                </div>
                                
                                {{-- Project is optional, can be removed if confusing --}}
                                <input type="hidden" name="project_id" :value="null">

                            </div>

                            <hr class="my-6">

                            <h3 class="text-lg font-semibold mb-2">Items Received</h3>
                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="item.tempId">
                                    <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
                                        <div class="flex-1">
                                            <label :for="`item_id_${item.tempId}`" class="block font-medium text-sm text-gray-700">{{ __('Item') }}</label>
                                            <select :id="`item_id_${item.tempId}`" :name="`items[${index}][inventory_item_id]`" class="grn-item-select block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required x-init="initializeTomSelect($el, true, index)">
                                                <option value="">Select an item</option>
                                                @foreach ($inventoryItems as $invItem)
                                                    <option value="{{ $invItem->id }}" data-cost="{{ $invItem->base_purchase_price }}">{{ $invItem->item_code }} - {{ $invItem->item_name }} ({{ $invItem->uom }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-24">
                                            <label :for="`item_qty_${item.tempId}`" class="block font-medium text-sm text-gray-700">{{ __('Qty Received') }}</label>
                                            <input x-model.number="item.quantity_received" :id="`item_qty_${item.tempId}`" class="block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm text-right" type="number" :name="`items[${index}][quantity_received]`" min="0.01" step="0.01" required />
                                        </div>
                                        <div class="w-32">
                                            <label :for="`item_cost_${item.tempId}`" class="block font-medium text-sm text-gray-700">{{ __('Unit Cost (Rp)') }}</label>
                                            <input x-model.number="item.unit_cost" :id="`item_cost_${item.tempId}`" class="block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm text-right" type="number" :name="`items[${index}][unit_cost]`" min="0" step="0.01" required />
                                        </div>
                                        <div class="w-32">
                                            <label class="block font-medium text-sm text-gray-700">{{ __('Subtotal') }}</label>
                                            <span class="block mt-1 w-full pt-2 text-sm text-right font-semibold" x-text="formatCurrency(item.quantity_received * item.unit_cost)"></span>
                                        </div>
                                        <div class="pt-5">
                                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 font-bold p-2"> X </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button type="button" @click="addItem()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Add Line Item</button>

                            <hr class="my-6">

                            <div class="flex justify-end">
                                <div class="w-64">
                                    <div class="flex justify-between">
                                        <span class="font-bold text-lg">Total Amount:</span>
                                        <span class="font-bold text-lg" x-text="formatCurrency(total)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6 border-t pt-4 space-x-3">
                                <a href="{{ route('goods-receipts.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md"> {{ __('Cancel') }} </a>
                                <x-primary-button> {{ __('Post Receipt') }} </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function grnForm() {
            return {
                items: [{ tempId: Date.now(), inventory_item_id: '', quantity_received: 1, unit_cost: 0 }],
                tomSelectInstances: {},
                
                newItem() {
                     return { tempId: Date.now() + '-' + Math.random().toString(36).substr(2, 5), inventory_item_id: '', quantity_received: 1, unit_cost: 0 };
                },
                addItem() {
                    this.items.push(this.newItem());
                },
                removeItem(index) {
                    let item = this.items[index];
                    if (item) {
                        this.destroySelect(`item_id_${item.tempId}`);
                    }
                    if (index >= 0 && index < this.items.length) {
                         this.items.splice(index, 1);
                    }
                },
                get total() {
                    return this.items.reduce((sum, item) => {
                        return sum + ((parseFloat(item.quantity_received) || 0) * (parseFloat(item.unit_cost) || 0));
                    }, 0);
                },
                initializeTomSelect(element, isItemSelect = false, itemIndex = null) {
                     if (element && !element.tomselect) {
                        let self = this;
                        let instance = new TomSelect(element, {
                            create: false,
                            sortField: { field: "text", direction: "asc" },
                            onChange: function(value) {
                                if (isItemSelect && itemIndex !== null && self.items[itemIndex]) {
                                    self.items[itemIndex].inventory_item_id = value;
                                    const selectedOption = this.getOption(value);
                                    if(selectedOption) {
                                        self.items[itemIndex].unit_cost = parseFloat(selectedOption.dataset.cost) || 0;
                                    }
                                }
                            }
                        });
                        this.tomSelectInstances[element.id] = instance;
                     }
                },
                destroySelect(elementId) {
                     if (elementId && this.tomSelectInstances[elementId]) {
                         this.tomSelectInstances[elementId].destroy();
                         delete this.tomSelectInstances[elementId];
                     }
                },
                formatCurrency(value) {
                     if (isNaN(value)) return 'Rp 0';
                    return parseFloat(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 2 });
                }
            }
        }
    </script>
</x-app-layout>