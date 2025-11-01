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

                    <div x-data="rabBuilder(@json($ahsJsonData), @json($workTypesLibrary_json), @json($oldItemsArray))">

                        <form method="POST" action="{{ route('quotations.store') }}">
                            @csrf
                            <input type="hidden" name="items_json" :value="JSON.stringify(items)">

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                <div>
                                    <label for="client_id" class="block font-medium text-sm text-gray-700">{{ __('Client') }}</label>
                                    <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Select a client</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="project_name" class="block font-medium text-sm text-gray-700">{{ __('Project Name') }}</label>
                                    <input id="project_name" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="project_name" value="{{ old('project_name') }}" required />
                                </div>
                                <div>
                                    <label for="sub_project_name" class="block font-medium text-sm text-gray-700">{{ __('Sub Project (Optional)') }}</label>
                                    <input id="sub_project_name" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="sub_project_name" value="{{ old('sub_project_name') }}" />
                                </div>
                                <div>
                                    <label for="date" class="block font-medium text-sm text-gray-700">{{ __('Date') }}</label>
                                    <input id="date" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required />
                                </div>
                            </div>

                            <hr class="my-6">
                            
                            <div class="bg-gray-50 border rounded-md p-4 mb-6">
                                <label for="pull-work-type" class="block text-sm font-medium text-gray-700">Pull from Library</label>
                                <select id="pull-work-type" placeholder="Select a Work Type to add it..." @change="pullWorkType($event)">
                                    <option value="">-- Select a Work Type to add it... --</option>
                                    @foreach($workTypesLibrary_json as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Selecting a Work Type will automatically add it and all its items to the list below.</p>
                            </div>


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
                                            <input x-model="item.item_code" :id="`item_code_${level}_${index}`" type="text"
                                                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm bg-gray-100 italic"
                                                    placeholder="Code" readonly disabled>
                                        </div>
                                        <div class="col-span-1 pt-1">
                                            <input x-model="item.uom" :id="`uom_${level}_${index}`" type="text"
                                                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm bg-gray-100 italic"
                                                    placeholder="Unit" readonly disabled>
                                        </div>
                                        <div class="col-span-2 pt-1">
                                            <input x-model.number="item.quantity" :id="`qty_${level}_${index}`" type="number" step="0.01" class="block w-full text-sm text-right border-gray-300 rounded-md shadow-sm" placeholder="0" :disabled="isParent">
                                        </div>
                                        <div class="col-span-2 pt-1">
                                            <input x-model.number="item.unit_price" :id="`price_${level}_${index}`" type="number" step="0.01"
                                                    class="block w-full text-sm text-right border-gray-300 rounded-md shadow-sm bg-gray-100 italic" placeholder="0" readonly disabled>
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
                                <template x-for="(item, index) in items" :key="item.id">
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
                                <a href="{{ route('quotations.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50"> {{ __('Cancel') }} </a>
                                <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"> {{ __('Save Quotation') }} </button>
                            </div>
                        </form>
                    </div> 
                </div>
            </div>
        </div>
    </div>

    @push('head-scripts')
    <script>
        // MODIFIED: Pass in library data
        window.rabBuilder = function(ahsLibraryData, workTypesLibrary, oldItems) {
            return {
                items: oldItems || [],
                ahsData: ahsLibraryData,
                library: {
                    workTypes: workTypesLibrary
                },
                initSelectCounter: 0,
                pullWorkTypeSelect: null, // To hold the Tom Select instance

                // --- NEW PULL FUNCTION ---
                pullWorkType(event) {
                    const workTypeId = event.target.value;
                    if (!workTypeId) return;

                    const workType = this.library.workTypes.find(wt => wt.id == workTypeId);
                    if (!workType) {
                        alert('Error: Could not find Work Type data.');
                        return;
                    }
                    
                    // 1. Create the main parent item (the Work Type)
                    let newWorkTypeItem = this.newItem(true);
                    newWorkTypeItem.description = workType.name;

                    // 2. Loop through its Work Items
                    workType.work_items.forEach(workItem => {
                        let newWorkItem = this.newItem(true); // Create sub-parent
                        newWorkItem.description = workItem.name;
                        
                        // 3. Loop through its AHS components
                        workItem.unit_rate_analyses.forEach(ahs => {
                            let newAhsItem = this.newItem(false); // Create AHS line
                            newAhsItem.unit_rate_analysis_id = ahs.id;
                            newAhsItem.description = ahs.name;
                            newAhsItem.item_code = ahs.code;
                            newAhsItem.uom = ahs.unit;
                            newAhsItem.unit_price = parseFloat(ahs.total_cost) || 0;
                            newAhsItem.quantity = 1; // Default qty to 1
                            newWorkItem.children.push(newAhsItem);
                        });
                        
                        if(newWorkItem.children.length > 0) {
                            newWorkTypeItem.children.push(newWorkItem);
                        }
                    });

                    // 4. Add the fully built Work Type to the main 'items' list
                    if(newWorkTypeItem.children.length > 0) {
                        this.items.push(newWorkTypeItem);
                        // Re-initialize any new .ahs-select dropdowns
                        this.$nextTick(() => { 
                            document.querySelectorAll('.ahs-select').forEach(el => this.initializeSelects(el));
                        });
                    } else {
                        alert('This Work Type has no Work Items with AHS data to import.');
                    }
                    
                    // 5. Reset the dropdown
                    if (this.pullWorkTypeSelect) {
                        this.pullWorkTypeSelect.clear();
                    }
                },

                // --- YOUR ORIGINAL FUNCTIONS (Unchanged, but use this.ahsData) ---
                addRootItem() { this.items.push(this.newItem(true)); },
                
                addSubItem(parentItem) {
                    const findParent = (items, targetId) => { for (let item of items) { if (item.id === targetId) return item; if (item.children && item.children.length > 0) { let found = findParent(item.children, targetId); if (found) return found; } } return null; };
                    let actualParent = findParent(this.items, parentItem.id);
                    if (actualParent) { 
                        if (!actualParent.children) actualParent.children = []; 
                        actualParent.children.push(this.newItem(false)); 
                        actualParent.open = true; 
                        actualParent.isParent = true;
                        this.$nextTick(() => { 
                            document.querySelectorAll('.ahs-select').forEach(el => this.initializeSelects(el));
                        }); 
                    }
                },
                
                removeItem(itemToRemove) {
                    const findAndRemove = (items, targetId) => { for (let i = 0; i < items.length; i++) { if (items[i].id === targetId) { this.destroyItemSelects(items[i]); items.splice(i, 1); return true; } if (items[i].children && items[i].children.length > 0) { if (findAndRemove(items[i].children, targetId)) return true; } } return false; };
                    findAndRemove(this.items, itemToRemove.id);
                },
                
                newItem(isParent = false) {
                    return { id: `temp_${Date.now()}_${Math.random()}`, unit_rate_analysis_id: null, description: '', item_code: '', uom: '', quantity: isParent ? null : 0, unit_price: isParent ? null : 0, children: [], open: true, isParent: isParent };
                },
                
                toggleChildren(item) {
                    item.open = !item.open;
                },
                
                linkAHS(item, event) {
                    const findAndUpdate = (items, targetId, event) => {
                        for (let currentItem of items) {
                            if (currentItem.id === targetId) {
                                const selectedId = event.target.value;
                                // MODIFIED: Use this.ahsData
                                if (selectedId && this.ahsData[selectedId]) { 
                                    const ahsData = this.ahsData[selectedId]; 
                                    currentItem.description = ahsData.name;
                                    currentItem.item_code = ahsData.code;
                                    currentItem.uom = ahsData.unit;
                                    currentItem.unit_price = parseFloat(ahsData.cost) || 0;
                                    currentItem.unit_rate_analysis_id = selectedId;
                                } else {
                                    currentItem.description = '';
                                    currentItem.item_code = '';
                                    currentItem.uom = '';
                                    currentItem.unit_price = 0;
                                    currentItem.unit_rate_analysis_id = null;
                                } return true;
                            }
                            if (currentItem.children && currentItem.children.length > 0) { if (findAndUpdate(currentItem.children, targetId, event)) return true; }
                        } return false;
                    }; 
                    findAndUpdate(this.items, item.id, event);
                },
                
                calculateItemTotal(item) {
                    const findItemById = (items, targetId) => { for (let i of items) { if (i.id === targetId) return i; if (i.children) { let found = findItemById(i.children, targetId); if (found) return found; } } return null; }; 
                    const actualItem = findItemById(this.items, item.id); 
                    if (!actualItem) return 0; 
                    actualItem.isParent = (actualItem.children && actualItem.children.length > 0); 
                    if (actualItem.isParent) { 
                        return actualItem.children.reduce((sum, child) => sum + this.calculateItemTotal(child), 0); 
                    } else { 
                        return (parseFloat(actualItem.quantity) || 0) * (parseFloat(actualItem.unit_price) || 0); 
                    }
                },
                
                get grandTotal() {
                    return this.items.reduce((sum, item) => sum + this.calculateItemTotal(item), 0);
                },
                
                formatCurrency(value) {
                    if (isNaN(value)) return 'Rp 0'; 
                    return parseFloat(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },
                
                initializeSelects(element) {
                    if (typeof TomSelect === 'undefined') return;
                    if (element && !element.tomselect && element.matches('.ahs-select')) { 
                        this.initSelectCounter++; 
                        if (!element.id) { element.id = `tomselect-ahs-${this.initSelectCounter}`; } 
                        new TomSelect(element, { create: false, sortField: { field: "text", direction: "asc" } }); 
                    }
                },
                
                destroySelect(element) {
                    if (element && element.tomselect) { element.tomselect.destroy(); }
                },
                
                destroyItemSelects(item) {
                    if (item.children && item.children.length > 0) { 
                        item.children.forEach(child => this.destroyItemSelects(child)); 
                    }
                },
                
                init() {
                    // Initialize the new "Pull" dropdown
                    if (typeof TomSelect !== 'undefined') {
                        this.pullWorkTypeSelect = new TomSelect('#pull-work-type', { create: false });
                    }

                    // Your original init logic
                    if (this.items.length === 0) { 
                        this.addRootItem(); 
                    } else {
                        const setIsParent = (items) => {
                            items.forEach(item => {
                                item.isParent = (item.children && item.children.length > 0);
                                if (item.isParent) { setIsParent(item.children); }
                            });
                        }; 
                        setIsParent(this.items);
                        this.$nextTick(() => { 
                            document.querySelectorAll('.ahs-select').forEach(el => this.initializeSelects(el));
                        });
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>