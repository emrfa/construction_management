<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Quotation (RAB / BOQ)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div x-data="rabBuilder()">

                        <form method="POST" action="{{ route('quotations.store') }}">
                            @csrf
                            <input type="hidden" name="items_json" :value="JSON.stringify(items)">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <x-input-label for="client_id" :value="__('Client')" />
                                    <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Select a client</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="project_name" :value="__('Project Name')" />
                                    <x-text-input id="project_name" class="block mt-1 w-full" type="text" name="project_name" :value="old('project_name')" required />
                                </div>
                                <div>
                                    <x-input-label for="date" :value="__('Date')" />
                                    <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', date('Y-m-d'))" required />
                                </div>
                            </div>

                            <hr class="my-6">

                            <h3 class="text-lg font-semibold mb-2">Quotation Items (WBS)</h3>
                            <div class="grid grid-cols-12 gap-2 text-sm font-bold text-gray-600 uppercase px-2 py-1 border-b">
                                <div class="col-span-4">Description / AHS Item</div>
                                <div class="col-span-1">Code</div>
                                <div class="col-span-1">Unit</div>
                                <div class="col-span-2 text-right">Quantity</div>
                                <div class="col-span-2 text-right">Unit Price (Rp)</div>
                                <div class="col-span-2 text-right">Subtotal (Rp)</div>
                            </div>

                            <script type="text/template" x-ref="itemTemplate">
                                <div class="space-y-1 py-1 border-b border-gray-100 last:border-b-0">
                                    <div class="grid grid-cols-12 gap-2 items-start" :class="{ 'bg-gray-50 p-2 rounded border': isParent }">
                                        <div class="col-span-4">
                                            <div class="flex items-start">
                                                <button type="button" @click="toggleChildren(item)"
                                                        class="text-gray-500 w-5 mr-1 pt-1 flex-shrink-0"
                                                        x-show="isParent || item.children.length > 0">
                                                    <span x-show="!item.open">▶</span>
                                                    <span x-show="item.open">▼</span>
                                                </button>
                                                <div class="flex-1 space-y-1">
    {{-- Use x-if to completely remove the select for parents --}}
    <template x-if="!isParent">
        <div>
            <label :for="`ahs_id_${level}_${index}`" class="sr-only">AHS Item</label>
            <select x-model="item.unit_rate_analysis_id"
                    @change="linkAHS(item, $event)"
                    :id="`ahs_id_${level}_${index}`"
                    class="ahs-select block w-full border-gray-300 text-sm rounded-md shadow-sm"
                    x-init="initializeSelects($el)">
                <option value="">-- Select AHS Item --</option>
                @foreach ($ahsLibrary as $ahs)
                    <option value="{{ $ahs->id }}" data-code="{{ $ahs->code }}" data-name="{{ $ahs->name }}" data-unit="{{ $ahs->unit }}" data-cost="{{ $ahs->total_cost }}">{{ $ahs->name }}</option>
                @endforeach
            </select>
        </div>
    </template>

    {{-- Use x-if to completely remove the text input for children --}}
    <template x-if="isParent">
        <div>
            <label :for="`desc_${level}_${index}`" class="sr-only">Section Title</label>
            <input x-model="item.description" type="text"
                    :id="`desc_${level}_${index}`"
                    class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-bold"
                    placeholder="Section Title" required>
        </div>
    </template>
                                                    <input type="hidden" :name="`items[${index}][description]`" x-model="item.description">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-span-1 pt-1">
                                            <label :for="`item_code_${level}_${index}`" class="sr-only">Code</label>
                                            <input x-model="item.item_code" :id="`item_code_${level}_${index}`" type="text" {{-- Removed name --}}
                                                   class="block w-full text-sm border-gray-300 rounded-md shadow-sm bg-gray-100 italic"
                                                   placeholder="Code" readonly disabled> {{-- Code is always readonly from AHS --}}
                                        </div>
                                        <div class="col-span-1 pt-1">
                                            <label :for="`uom_${level}_${index}`" class="sr-only">Unit</label>
                                            <input x-model="item.uom" :id="`uom_${level}_${index}`" type="text" {{-- Removed name --}}
                                                   class="block w-full text-sm border-gray-300 rounded-md shadow-sm bg-gray-100 italic"
                                                   placeholder="Unit" readonly disabled> {{-- Unit is always readonly from AHS --}}
                                        </div>
                                        <div class="col-span-2 pt-1">
                                            <label :for="`qty_${level}_${index}`" class="sr-only">Quantity</label>
                                            <input x-model.number="item.quantity" :id="`qty_${level}_${index}`" type="number" step="0.01" {{-- Removed name --}} class="block w-full text-sm text-right border-gray-300 rounded-md shadow-sm" placeholder="0" :disabled="isParent">
                                        </div>
                                        <div class="col-span-2 pt-1">
                                             <label :for="`price_${level}_${index}`" class="sr-only">Unit Price</label>
                                            <input x-model.number="item.unit_price" :id="`price_${level}_${index}`" type="number" step="0.01" {{-- Removed name --}}
                                                   class="block w-full text-sm text-right border-gray-300 rounded-md shadow-sm bg-gray-100 italic" placeholder="0" readonly disabled> {{-- Price is always readonly from AHS --}}
                                        </div>
                                        <div class="col-span-2 text-right pt-2 text-sm font-semibold">
                                            <span x-text="formatCurrency(calculateItemTotal(item))"></span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-12 gap-2">
                                         <div class="col-start-1 col-span-12 flex items-center space-x-2 pl-6 text-xs">
                                             <button type="button" @click="addSubItem(item)" class="text-blue-600 hover:text-blue-800"> + Add Sub-Item </button>
                                             <button type="button" @click="removeItem(item)" class="text-red-600 hover:text-red-800"> Remove </button>
                                         </div>
                                    </div>

                                    <div class="pl-6 space-y-1" x-show="item.open && item.children.length > 0">
                                        <template x-for="(childItem, childIndex) in item.children" :key="childItem.id">
                                            <div x-html="$refs.itemTemplate.innerHTML" x-data="{ item: childItem, index: childIndex, parentArray: item.children, level: level + 1, isParent: false }"></div>
                                        </template>
                                    </div>
                                </div>
                            </script>

                            <div id="root-items" class="space-y-1">
                                <template x-for="(item, index) in items" :key="item.id"> {/* FIX: Use item.id for key */}
                                    <div x-html="$refs.itemTemplate.innerHTML" x-data="{ item: item, index: index, parentArray: items, level: 0, isParent: item.isParent ?? true }"></div>
                                </template>
                            </div>

                            <button type="button" @click="addRootItem()" class="mt-4 text-sm text-blue-600 hover:text-blue-800 font-semibold">
                                + Add Section / Root Item
                            </button>

                            <hr class="my-6">
                            <div class="flex justify-end">
                                <div class="w-64">
                                    <div class="flex justify-between font-bold text-lg border-t-2 pt-2">
                                        <span>Total Estimate:</span>
                                        <span x-text="formatCurrency(grandTotal)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6 border-t pt-4">
                                <a href="{{ route('quotations.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md"> {{ __('Cancel') }} </a>
                                <x-primary-button class="ml-4"> {{ __('Save Quotation') }} </x-primary-button>
                            </div>
                        </form>
                    </div> </div>
            </div>
        </div>
    </div>

    @php
        $ahsJsonData = $ahsLibrary->mapWithKeys(fn($ahs) => [$ahs->id => [
            'code' => $ahs->code,
            'name' => $ahs->name,
            'unit' => $ahs->unit,
            'cost' => $ahs->total_cost
        ]]);
        $oldItemsArray = old('items_json') ? json_decode(old('items_json'), true) : array();
        $oldCount = count($oldItemsArray);
    @endphp

    <script>
        const ahsLibraryData = @json($ahsJsonData);

        function rabBuilder() {
            return {
                items: @json($oldItemsArray),
                initSelectCounter: 0,

                addRootItem() { this.items.push(this.newItem(true)); },
                addSubItem(parentItem) {
                    const findParent = (items, targetId) => { for (let item of items) { if (item.id === targetId) return item; if (item.children && item.children.length > 0) { let found = findParent(item.children, targetId); if (found) return found; } } return null; };
                    let actualParent = findParent(this.items, parentItem.id);
                    if (actualParent) { if (!actualParent.children) actualParent.children = []; actualParent.children.push(this.newItem(false)); actualParent.open = true; this.$nextTick(() => { this.initializeSelects('.ahs-select'); }); }
                },
                removeItem(itemToRemove) {
                     const findAndRemove = (items, targetId) => { for (let i = 0; i < items.length; i++) { if (items[i].id === targetId) { this.destroyItemSelects(items[i]); items.splice(i, 1); return true; } if (items[i].children && items[i].children.length > 0) { if (findAndRemove(items[i].children, targetId)) return true; } } return false; };
                     findAndRemove(this.items, itemToRemove.id);
                },
                newItem(isParent = false) {
                     return { id: Date.now() + Math.random(), unit_rate_analysis_id: null, description: '', item_code: '', uom: '', quantity: isParent ? null : 0, unit_price: isParent ? null : 0, children: [], open: true, isParent: isParent };
                },
                toggleChildren(item) {
                    const findAndToggle = (items, targetId) => { for (let currentItem of items) { if (currentItem.id === targetId) { currentItem.open = !currentItem.open; return true; } if (currentItem.children && currentItem.children.length > 0) { if (findAndToggle(currentItem.children, targetId)) return true; } } return false; }; findAndToggle(this.items, item.id);
                },
                linkAHS(item, event) {
                      const findAndUpdate = (items, targetId, event) => {
                         for (let currentItem of items) {
                             if (currentItem.id === targetId) {
                                 const selectedId = event.target.value;
                                 if (selectedId && ahsLibraryData[selectedId]) {
                                     const ahsData = ahsLibraryData[selectedId];
                                     currentItem.description = ahsData.name; // Set description internally
                                     currentItem.item_code = ahsData.code;
                                     currentItem.uom = ahsData.unit;
                                     currentItem.unit_price = parseFloat(ahsData.cost) || 0;
                                     currentItem.unit_rate_analysis_id = selectedId;
                                 } else { // Clear if "-- Select --"
                                     currentItem.description = '';
                                     currentItem.item_code = '';
                                     currentItem.uom = '';
                                     currentItem.unit_price = 0;
                                     currentItem.unit_rate_analysis_id = null;
                                 } return true;
                             }
                             if (currentItem.children && currentItem.children.length > 0) { if (findAndUpdate(currentItem.children, targetId, event)) return true; }
                         } return false;
                     }; findAndUpdate(this.items, item.id, event);
                },
                calculateItemTotal(item) { /* Same recursive logic as before */
                    const findItemById = (items, targetId) => { /*...*/ }; const actualItem = findItemById(this.items, item.id); if (!actualItem) return 0; actualItem.isParent = (actualItem.children && actualItem.children.length > 0); if (actualItem.isParent) { return actualItem.children.reduce((sum, child) => sum + this.calculateItemTotal(child), 0); } else { return (parseFloat(actualItem.quantity) || 0) * (parseFloat(actualItem.unit_price) || 0); }
                },
                get grandTotal() { /* Same as before */
                    return this.items.reduce((sum, item) => sum + this.calculateItemTotal(item), 0);
                 },
                 formatCurrency(value) { /* Same as before */
                     if (isNaN(value)) return 'Rp 0'; return parseFloat(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 2 });
                 },
                 initializeSelects(element) { /* Same as before */
                     if (element && !element.tomselect && element.matches('.ahs-select')) { this.initSelectCounter++; if (!element.id) { element.id = `tomselect-ahs-${this.initSelectCounter}`; } new TomSelect(element, { create: false, sortField: { field: "text", direction: "asc" } }); }
                 },
                 destroySelect(element) { /* Same as before */
                     if (element && element.tomselect) { element.tomselect.destroy(); }
                 },
                 destroyItemSelects(item) { /* Same as before */
                     if (item.children && item.children.length > 0) { item.children.forEach(child => this.destroyItemSelects(child)); }
                 },
                init() { /* Same logic as before using $oldCount */
                    @php $oldCount = count($oldItemsArray); @endphp
                    if (this.items.length === 0 && {{ $oldCount }} === 0) { this.addRootItem(); }
                    else if (this.items.length > 0) { // If items loaded from old()
                        const setIsParent = (items) => { /*...*/ }; setIsParent(this.items);
                        this.$nextTick(() => { /* Same TomSelect init */ });
                    } else { this.$nextTick(() => { /* Same TomSelect init */ }); }
                }
            }
        }
    </script>
</x-app-layout>