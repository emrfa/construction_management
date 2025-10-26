<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Quotation (RAB / BOQ)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- FIX: Changed x-data to call our new function -->
                <div class="p-6 text-gray-900" x-data="quotationEditor()">

                    <!-- FIX: All the functions (items, addNewItem, etc.) are now defined
                         in the <script> block at the bottom of this file. 
                         The rest of the form works without any changes. -->

                    <form method="POST" action="{{ route('quotations.update', $quotation) }}">
                         @method('PUT')
                         @csrf
                        
                        <input type="hidden" name="items_json" :value="JSON.stringify(items)">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="client_id" :value="__('Client')" />
                                <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select a client</option>
                                    
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" {{ $quotation->client_id == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                    
                                </select>
                            </div>
                            <div>
                                <x-input-label for="project_name" :value="__('Project Name')" />
                                <x-text-input id="project_name" class="block mt-1 w-full" type="text" name="project_name" :value="old('project_name', $quotation->project_name)" required />
                            </div>
                            <div>
                                <x-input-label for="date" :value="__('Date')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', $quotation->date)" required />
                            </div>
                        </div>

                        <hr class="my-6">

                        <h3 class="text-lg font-semibold mb-2">Quotation Items</h3>
                        <div class="grid grid-cols-12 gap-2 text-sm font-bold text-gray-600 uppercase px-2 py-1">
                            <div class="col-span-5">Description</div>
                            <div class="col-span-1">Code</div>
                            <div class="col-span-1">UoM</div>
                            <div class="col-span-1">Qty</div>
                            <div class="col-span-2">Unit Price</div>
                            <div class="col-span-2">Subtotal</div>
                        </div>

                        <script type="text/template" x-ref="itemTemplate">
                            <div class="space-y-1">
                                <div class="grid grid-cols-12 gap-2 items-start" :class="{ 'bg-gray-50 p-2 rounded': item.children.length > 0 }">
                                    
                                    <div class="col-span-5">
                                        <div class="flex items-center">
                                            <button type="button" @click="item.open = !item.open" class="text-gray-500 w-5" x-show="item.children.length > 0">
                                                <span x-show="!item.open">+</span>
                                                <span x-show="item.open">-</span>
                                            </button>
                                            <input x-model="item.description" type="text" 
                                                   class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                                   :class="{ 'font-bold': item.children.length > 0 }"
                                                   placeholder="Item Description" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-span-1">
                                        <input x-model="item.item_code" type="text" class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Code">
                                    </div>
                                    
                                    <div class="col-span-1">
                                        <input x-model="item.uom" type="text" class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="M2">
                                    </div>
                                    
                                    <div class="col-span-1">
                                        <input x-model.number="item.quantity" type="number" step="0.01" class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="0" :disabled="item.children.length > 0">
                                    </div>
                                    
                                    <div class="col-span-2">
                                        <input x-model.number="item.unit_price" type="number" step="0.01" class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="0" :disabled="item.children.length > 0">
                                    </div>
                                    
                                    <div class="col-span-2">
                                        <span class="block pt-2 text-sm font-semibold"
                                              x-text="getItemTotal(item).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 })">
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-12 gap-2">
                                    <div class="col-start-6 col-span-7 flex items-center space-x-2 pl-5">
                                        <button type="button" @click="addNewItem(item.children)" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200">
                                            + Add Sub-Item
                                        </button>
                                        <button type="button" @click="removeItem(parentArray, index)" class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">
                                            Remove Item
                                        </button>
                                    </div>
                                </div>

                                <div class="pl-6 space-y-2" x-show="item.open && item.children.length > 0">
                                    <template x-for="(childItem, childIndex) in item.children" :key="childIndex">
                                        <div x-html="$refs.itemTemplate.innerHTML" x-data="{ item: childItem, index: childIndex, parentArray: item.children }"></div>
                                    </template>
                                </div>
                            </div>
                        </script>

                        <div class="space-y-2" id="root-items">
                            <template x-for="(item, index) in items" :key="index">
                                <div x-html="$refs.itemTemplate.innerHTML" x-data="{ item: item, index: index, parentArray: items }"></div>
                            </template>
                        </div>

                        <div class="mt-4">
                            <button type="button" @click="addNewItem(items)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">
                                + Add Section
                            </button>
                        </div>

                        <hr class="my-6">

                        <div class="flex justify-end">
                            <div class="w-64">
                                <div class="flex justify-between">
                                    <span class="font-bold text-lg">Total Estimate:</span>
                                    <span class="font-bold text-lg" 
                                          x-text="total.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 })">
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('quotations.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ml-4">
                                {{ __('Update Quotation') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- FIX: Added this <script> block to safely define the Alpine component -->
    <script>
        function quotationEditor() {
            return {
                items: @json($itemsTree),

                addNewItem(parentArray) {
                    parentArray.push({
                        description: '',
                        item_code: '',
                        uom: '',
                        quantity: 0,
                        unit_price: 0,
                        children: [],
                        open: true 
                    });
                },
                
                removeItem(parentArray, index) {
                    parentArray.splice(index, 1);
                },

                getItemTotal(item) {
                    if (item.children.length > 0) {
                        let sum = 0;
                        item.children.forEach(child => {
                            sum += this.getItemTotal(child); // Recursive call
                        });
                        return sum;
                    } else {
                        return (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
                    }
                },

                get total() {
                    let sum = 0;
                    this.items.forEach(item => {
                        sum += this.getItemTotal(item);
                    });
                    return sum;
                }
            }
        }
    </script>
</x-app-layout>