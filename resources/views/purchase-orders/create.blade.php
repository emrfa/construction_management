<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Purchase Orders', 'url' => route('purchase-orders.index')],
            ['label' => 'New PO', 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Purchase Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

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

                    {{-- Use the same Alpine setup as your edit page --}}
                    <div x-data="poForm()"> 

                        <form method="POST" action="{{ route('purchase-orders.store') }}">
                            @csrf
                            <input type="hidden" name="items_json" :value="JSON.stringify(items.map(item => ({ id: item.id, inventory_item_id: item.inventory_item_id, quantity_ordered: item.quantity_ordered, unit_cost: item.unit_cost })))">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="supplier_id" :value="__('Supplier')" />
                                    <select id="supplier_id" name="supplier_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Select a supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="order_date" :value="__('Order Date')" />
                                    <x-text-input id="order_date" class="block mt-1 w-full" type="date" name="order_date" :value="old('order_date', date('Y-m-d'))" required />
                                </div>

                                <div>
                                    <x-input-label for="expected_delivery_date" :value="__('Expected Delivery (Optional)')" />
                                    <x-text-input id="expected_delivery_date" class="block mt-1 w-full" type="date" name="expected_delivery_date" :value="old('expected_delivery_date')" />
                                </div>
                            </div>

                            <hr class="my-6">

                            <h3 class="text-lg font-semibold mb-2">Order Items</h3>
                            <div class="space-y-4">

                                <template x-for="(item, index) in items" :key="item.tempId">
                                    <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
                                        <div class="flex-1">
                                            <label :for="`item_id_${item.tempId}`" class="block font-medium text-sm text-gray-700">{{ __('Item') }}</label>
                                            <select :id="`item_id_${item.tempId}`" :name="`items[${index}][inventory_item_id]`" class="po-item-select block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required x-init="initializeItemSelect($el, index)">
                                                <option value="">Select an item</option>
                                                @foreach ($inventoryItems as $invItem)
                                                    <option value="{{ $invItem->id }}">{{ $invItem->item_code }} - {{ $invItem->item_name }} ({{ $invItem->uom }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-24">
                                            <label :for="`item_qty_${item.tempId}`" class="block font-medium text-sm text-gray-700">{{ __('Qty Ordered') }}</label>
                                            <input x-model.number="item.quantity_ordered" :id="`item_qty_${item.tempId}`" class="block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm text-right" type="number" :name="`items[${index}][quantity_ordered]`" min="0.01" step="0.01" required />
                                        </div>
                                        <div class="w-32">
                                            <label :for="`item_cost_${item.tempId}`" class="block font-medium text-sm text-gray-700">{{ __('Unit Cost (Rp)') }}</label>
                                            <input x-model.number="item.unit_cost" :id="`item_cost_${item.tempId}`" class="block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm text-right" type="number" :name="`items[${index}][unit_cost]`" min="0" step="0.01" required />
                                        </div>
                                        <div class="w-32">
                                            <label class="block font-medium text-sm text-gray-700">{{ __('Subtotal') }}</label>
                                            <span class="block mt-1 w-full pt-2 text-sm text-right font-semibold" x-text="formatCurrency(item.quantity_ordered * item.unit_cost)"></span>
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

                            {{-- NEW: Added button group --}}
                            <div class="flex items-center justify-end mt-6 border-t pt-4 space-x-3">
                                <a href="{{ route('purchase-orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md"> {{ __('Cancel') }} </a>
                                <x-primary-button> {{ __('Save as Draft') }} </x-primary-button>
                                 <button type="submit" name="mark_ordered" value="1" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Save & Mark Ordered
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Use the same JS from the edit page --}}
    <script>
        function poForm() {
            return {
                items: [],
                tomSelectInstances: {},
                initSelectCounter: 0,
                
                newItem() {
                     return { id: null, tempId: Date.now() + '-' + Math.random().toString(36).substr(2, 5), inventory_item_id: '', quantity_ordered: 1, unit_cost: 0 };
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
                        return sum + ((parseFloat(item.quantity_ordered) || 0) * (parseFloat(item.unit_cost) || 0));
                    }, 0);
                },
                 initializeItemSelect(element, index) {
                     const elementId = `item_id_${this.items[index]?.tempId}`;
                     element.id = elementId;

                     if (element && !this.tomSelectInstances[elementId]) {
                        let self = this;
                        let initialValue = this.items[index]?.inventory_item_id || '';
                        let instance = new TomSelect(element, {
                            placeholder: 'Select an item...',
                            items: initialValue ? [initialValue] : [],
                            onChange: function(value) {
                                if(self.items[index]) {
                                    self.items[index].inventory_item_id = value;
                                }
                            }
                        });
                         this.tomSelectInstances[elementId] = instance;
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
                 },
                init() {
                     if (this.items.length === 0) {
                        this.addItem();
                    }
                }
            }
        }
    </script>
</x-app-layout>