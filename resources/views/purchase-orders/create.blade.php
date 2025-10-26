<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Purchase Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div x-data="{
                        items: [ { inventory_item_id: '', quantity_ordered: 1, unit_cost: 0 } ],
                        addNewItem() {
                            this.items.push({ inventory_item_id: '', quantity_ordered: 1, unit_cost: 0 });
                        },
                        removeItem(index) {
                            this.items.splice(index, 1);
                        },
                        get total() {
                            return this.items.reduce((sum, item) => {
                                return sum + (parseFloat(item.quantity_ordered) * parseFloat(item.unit_cost));
                            }, 0);
                        }
                    }">

                        <form method="POST" action="{{ route('purchase-orders.store') }}">
                            @csrf

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
                                    <x-input-label for="expected_delivery_date" :value="__('Expected Delivery')" />
                                    <x-text-input id="expected_delivery_date" class="block mt-1 w-full" type="date" name="expected_delivery_date" :value="old('expected_delivery_date')" required />
                                </div>
                            </div>

                            <hr class="my-6">

                            <h3 class="text-lg font-semibold mb-2">Order Items</h3>
                            <div class="space-y-4">

                                <template x-for="(item, index) in items" :key="index">
                                    <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded">
                                        <div class="flex-1">
                                            <label :for="`item_id_${index}`" class="block font-medium text-sm text-gray-700">{{ __('Item') }}</label>
                                            <select x-model="item.inventory_item_id" :id="`item_id_${index}`" :name="`items[${index}][inventory_item_id]`" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                                <option value="">Select an item</option>
                                                @foreach ($inventoryItems as $invItem)
                                                    <option value="{{ $invItem->id }}">{{ $invItem->item_code }} - {{ $invItem->item_name }} ({{ $invItem->uom }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-24">
                                            <label :for="`item_qty_${index}`" class="block font-medium text-sm text-gray-700">{{ __('Qty Ordered') }}</label>
                                            <input x-model.number="item.quantity_ordered" :id="`item_qty_${index}`" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="number" :name="`items[${index}][quantity_ordered]`" min="0" step="0.01" required />
                                        </div>
                                        <div class="w-32">
                                            <label :for="`item_cost_${index}`" class="block font-medium text-sm text-gray-700">{{ __('Unit Cost (Rp)') }}</label>
                                            <input x-model.number="item.unit_cost" :id="`item_cost_${index}`" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="number" :name="`items[${index}][unit_cost]`" min="0" step="0.01" required />
                                        </div>
                                        <div class="w-32">
                                            <label class="block font-medium text-sm text-gray-700">{{ __('Subtotal') }}</label>
                                            <span class="block mt-1 w-full pt-2" x-text="(item.quantity_ordered * item.unit_cost).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 })"></span>
                                        </div>
                                        <div class="pt-5">
                                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 font-bold p-2"> X </button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4">
                                <button type="button" @click="addNewItem()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded"> + Add Line Item </button>
                            </div>

                            <hr class="my-6">

                            <div class="flex justify-end">
                                <div class="w-64">
                                    <div class="flex justify-between">
                                        <span class="font-bold text-lg">Total Amount:</span>
                                        <span class="font-bold text-lg" x-text="total.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 })"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('purchase-orders.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md"> {{ __('Cancel') }} </a>
                                <x-primary-button class="ml-4"> {{ __('Save Purchase Order') }} </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>