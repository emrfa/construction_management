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

                    {{-- FIX 1: Pass in the new workItemsLibrary_json variable --}}
                    <div x-data='rabBuilder(@json($ahsJsonData), @json($workTypesLibrary_json), @json($oldItemsArray), @json($workItemsLibrary_json))'>
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
                            

                            <h3 class="text-lg font-semibold mb-2">Quotation Items (WBS)</h3>
                                <div class="grid grid-cols-12 gap-2 text-sm font-bold text-gray-600 uppercase px-2 py-1 border-b">
                                    <div class="col-span-4">Description / AHS Item</div>
                                    <div class="col-span-1">Code</div>
                                    <div class="col-span-1">Unit</div>
                                    <div class="col-span-2 text-right">Quantity</div>
                                    <div class="col-span-2 text-right">Unit Price (Rp)</div>
                                    <div class="col-span-2 text-right">Subtotal (Rp)</div>
                                </div>

                                {{-- This is the recursive template for each item --}}
                                <script type="text/template" x-ref="itemTemplate">
                                    <div class="space-y-1 py-1 border-b border-gray-100 last:border-b-0">
                                        
                                        <div class="grid grid-cols-12 gap-2 items-start" :class="{ 'bg-gray-50 p-2 rounded border': item.isParent }">
                                            <div class="col-span-4">
                                                <div class="flex items-start">
                                                    <button type="button" @click="toggleChildren(item)"
                                                            class="text-gray-500 w-5 mr-1 pt-1 flex-shrink-0"
                                                            x-show="item.isParent">
                                                        <span x-show="!item.open">▶</span>
                                                        <span x-show="item.open">▼</span>
                                                    </button>
                                                    <div class="flex-1 space-y-1">
                                                        
                                                        <template x-if="!item.isParent">
                                                            <div>
                                                                <label :for="`ahs_id_${item.id}`" class="sr-only">AHS Item</label>
                                                                <select x-model="item.unit_rate_analysis_id"
                                                                        @change="linkAHS(item, $event)"
                                                                        :id="`ahs_id_${item.id}`"
                                                                        class="ahs-select block w-full border-gray-300 text-sm rounded-md shadow-sm"
                                                                        x-init="$nextTick(() => initializeSelects($el, true))">
                                                                    <option value="">-- Select AHS Item --</option>
                                                                    @foreach ($ahsLibrary as $ahs)
                                                                        <option value="{{ $ahs->id }}" data-code="{{ $ahs->code }}" data-name="{{ $ahs->name }}" data-unit="{{ $ahs->unit }}" data-cost="{{ $ahs->total_cost }}">{{ $ahs->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <input type="hidden" x-model="item.description" />
                                                            </div>
                                                        </template>

                                                        <template x-if="item.isParent">
                                                            <div class="space-y-2">
                                                                <label :for="`desc_${item.id}`" class="sr-only">Section Title</label>
                                                                <input x-model="item.description" type="text"
                                                                    :id="`desc_${item.id}`"
                                                                    class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm font-bold"
                                                                    placeholder="Section Title" required>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-span-1 pt-1">
                                                <input x-model="item.item_code" :id="`item_code_${item.id}`" type="text"
                                                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm bg-gray-100 italic"
                                                    placeholder="Code" readonly disabled>
                                            </div>
                                            <div class="col-span-1 pt-1">
                                                <input x-model="item.uom" :id="`uom_${item.id}`" type="text"
                                                    class="block w-full text-sm border-gray-300 rounded-md shadow-sm"
                                                    placeholder="Unit" :disabled="item.isParent || item.unit_rate_analysis_id"
                                                    :class="{'bg-gray-100 italic': item.isParent || item.unit_rate_analysis_id }">
                                            </div>
                                            <div class="col-span-2 pt-1">
                                                <input x-model.number="item.quantity" :id="`qty_${item.id}`" type="number" step="0.01" class="block w-full text-sm text-right border-gray-300 rounded-md shadow-sm" placeholder="0" :disabled="item.isParent">
                                            </div>
                                            <div class="col-span-2 pt-1">
                                                <input x-model.number="item.unit_price" :id="`price_${item.id}`" type="number" step="0.01"
                                                    class="block w-full text-sm text-right border-gray-300 rounded-md shadow-sm" 
                                                    placeholder="0" :disabled="item.isParent || item.unit_rate_analysis_id"
                                                    :class="{'bg-gray-100 italic': item.isParent || item.unit_rate_analysis_id }">
                                            </div>
                                            <div class="col-span-2 text-right pt-2 text-sm font-semibold">
                                                <span x-text="formatCurrency(calculateItemTotal(item))"></span>
                                            </div>
                                        </div>

                                        {{-- === ACTION BUTTONS (Inside the item) === --}}
                                        <div class="grid grid-cols-12 gap-2">
                                            <div class="col-start-1 col-span-12 flex items-center space-x-2 pl-6 text-xs">
                                                <template x-if="item.isParent">
                                                    <div class="flex items-center space-x-2">
                                                        
                                                        {{-- This is for BLANK SECTIONS (Sub-Projects) --}}
                                                        <template x-if="!item.isWorkType">
                                                            <select @change="addWorkType($event.target.value, item.children); $event.target.tomselect.clear();"
                                                                class="block text-xs border-gray-300 rounded-md shadow-sm py-1"
                                                                x-init="initializeSelects($el, false)">
                                                                <option value="">+ Add Work Type...</option>
                                                                @foreach($workTypesLibrary_json as $type)
                                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" @click="addWorkItem(item)" class="text-blue-600 hover:text-blue-800"> + Add Sub-Section </button>
                                                        </template>

                                                        {{-- FIX 3: This is for WORK TYPES --}}
                                                        <template x-if="item.isWorkType">
                                                            {{-- NEW: Select to add from library --}}
                                                            <select @change="addWorkItemFromLibrary($event.target.value, item.children); $event.target.tomselect.clear();"
                                                                class="block text-xs border-gray-300 rounded-md shadow-sm py-1"
                                                                x-init="initializeSelects($el, false)">
                                                                <option value="">+ Add Work Item from Library...</option>
                                                                {{-- This uses the NEW workItemsLibrary_json variable --}}
                                                                @foreach($workItemsLibrary_json as $workItem)
                                                                    <option value="{{ $workItem->id }}">{{ $workItem->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            {{-- KEPT: Button to add blank/manual work item --}}
                                                            <button type="button" @click="addWorkItem(item)" class="text-blue-600 hover:text-blue-800"> + Add Manual Work Item </button>
                                                        </template>

                                                        <button type="button" @click="addAHSItem(item)" class="text-green-600 hover:text-green-800"> + Add AHS Item </button>
                                                    </div>
                                                </template>
                                                <button type="button" @click="removeItem(item)" class="text-red-600 hover:text-red-800"> Remove </button>
                                            </div>
                                        </div>

                                        {{-- Recursive part for children --}}
                                        <div class="pl-6 space-y-1" x-show="item.open && item.children.length > 0">
                                            <template x-for="(childItem, childIndex) in item.children" :key="childItem.id">
                                                <div x-html="$refs.itemTemplate.innerHTML" x-data="{ 
                                                    item: childItem, 
                                                    index: childIndex, 
                                                    parentArray: item.children, 
                                                    level: level + 1,
                                                    isWorkType: childItem.isWorkType || false 
                                                }"></div>
                                            </template>
                                        </div>
                                    </div>
                                </script>

                                {{-- This is where the root items are rendered --}}
                                <div id="root-items" class="space-y-1">
                                    <template x-for="(item, index) in items" :key="item.id">
                                        <div x-html="$refs.itemTemplate.innerHTML" x-data="{ 
                                                item: item, 
                                                index: index, 
                                                parentArray: items, 
                                                level: 0,
                                                isWorkType: item.isWorkType || false
                                            }"></div>
                                    </template>
                                </div>

                                {{-- === ROOT ACTION BUTTONS (At the bottom of the list) === --}}
                                <div class="mt-4 flex space-x-4 border-t pt-4">
                                    <select @change="addWorkType($event.target.value, items); $event.target.tomselect.clear();"
                                        class="block text-sm border-gray-300 rounded-md shadow-sm py-2"
                                        x-init="initializeSelects($el, false)">
                                        <option value="">+ Add Work Type...</option>
                                        @foreach($workTypesLibrary_json as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>

                                    <button type="button" @click="addRootSection()" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                        + Add Section (Sub-Project)
                                    </button>
                                </div>

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
        // FIX 1: Add workItemsLibrary to the function signature
        window.rabBuilder = function(ahsLibraryData, workTypesLibrary, oldItems, workItemsLibrary) {
            return {
                items: oldItems || [],
                ahsData: ahsLibraryData,
                library: {
                    workTypes: workTypesLibrary,
                    workItems: workItemsLibrary // Add the new library
                },
                initSelectCounter: 0,

                init() {
                    if (typeof TomSelect === 'undefined') {
                        console.warn('TomSelect not loaded. Select dropdowns will not be enhanced.');
                    }
                    if (this.items.length === 0) { 
                        this.addRootSection(); 
                    } else {
                        // Re-hydrate data on validation error
                        const rehydrate = (items) => {
                            items.forEach(item => {
                                if (!item.isParent && item.unit_rate_analysis_id && this.ahsData[item.unit_rate_analysis_id]) {
                                    item.description = this.ahsData[item.unit_rate_analysis_id].name;
                                }
                                if (item.isParent && item.children && item.children.length > 0) {
                                    // A Work Type is a parent whose children are also parents (Work Items)
                                    item.isWorkType = item.children.some(child => child.isParent);
                                }
                                if (item.children) {
                                    rehydrate(item.children);
                                }
                            });
                        };
                        rehydrate(this.items);

                        this.$nextTick(() => { 
                            this.initializeAllSelects(document);
                        });
                    }
                },

                addWorkType(workTypeId, targetArray) {
                    if (!workTypeId) return;
                    const workType = this.library.workTypes.find(wt => wt.id == workTypeId);
                    if (!workType) return;

                    // 1. Create the main parent item for the Work Type
                    let newWorkTypeItem = this.newItem(true, true); // isParent: true, isWorkType: true
                    newWorkTypeItem.description = workType.name;

                    // 2. NEW: Check for and add DIRECT AHS links as line items
                    if (workType.unit_rate_analyses && workType.unit_rate_analyses.length > 0) {
                        workType.unit_rate_analyses.forEach(ahs => {
                            let newAhsItem = this.newItem(false, false); // isParent: false
                            newAhsItem.unit_rate_analysis_id = ahs.id;
                            newAhsItem.description = ahs.name; // Use AHS name directly
                            newAhsItem.item_code = ahs.code;
                            newAhsItem.uom = ahs.unit;
                            newAhsItem.unit_price = parseFloat(ahs.total_cost) || 0;
                            newAhsItem.quantity = 1; // Default to 1
                            newWorkTypeItem.children.push(newAhsItem); // Add to the Work Type's children
                        });
                    }

                    // 3. EXISTING: Check for and add CHILD WORK ITEMS as sub-parents
                    if (workType.work_items && workType.work_items.length > 0) {
                        workType.work_items.forEach(workItem => {
                            let newWorkItem = this.newItem(true, false); // isParent: true
                            newWorkItem.description = workItem.name;
                            
                            // Check if the work item has AHS links
                            if (workItem.unit_rate_analyses && workItem.unit_rate_analyses.length > 0) {
                                workItem.unit_rate_analyses.forEach(ahs => {
                                    let newAhsItem = this.newItem(false, false);
                                    newAhsItem.unit_rate_analysis_id = ahs.id;
                                    newAhsItem.description = ahs.name;
                                    newAhsItem.item_code = ahs.code;
                                    newAhsItem.uom = ahs.unit;
                                    newAhsItem.unit_price = parseFloat(ahs.total_cost) || 0;
                                    newAhsItem.quantity = 1;
                                    newWorkItem.children.push(newAhsItem);
                                });
                            }
                            
                            // Only add the child Work Item if it actually contains AHS items
                            if (newWorkItem.children.length > 0) {
                                newWorkTypeItem.children.push(newWorkItem);
                            }
                        });
                    }

                    // 4. Final check: Only add the Work Type if it produced any children
                    if (newWorkTypeItem.children.length > 0) {
                        targetArray.push(newWorkTypeItem);
                        this.$nextTick(() => { 
                            this.initializeAllSelects(this.$el);
                        });
                    } else {
                        // Updated alert message
                        alert('This Work Type has no direct AHS links and no child Work Items with AHS data to import.');
                    }
                },

                // FIX 2: NEW function to add a Work Item from the library
                addWorkItemFromLibrary(workItemId, targetArray) {
                    if (!workItemId) return;
                    const workItem = this.library.workItems.find(wi => wi.id == workItemId);
                    if (!workItem) return;

                    let newWorkItem = this.newItem(true, false); // isParent: true, isWorkType: false
                    newWorkItem.description = workItem.name;
                    
                    workItem.unit_rate_analyses.forEach(ahs => {
                        let newAhsItem = this.newItem(false, false);
                        newAhsItem.unit_rate_analysis_id = ahs.id;
                        newAhsItem.description = ahs.name;
                        newAhsItem.item_code = ahs.code;
                        newAhsItem.uom = ahs.unit;
                        newAhsItem.unit_price = parseFloat(ahs.total_cost) || 0;
                        newAhsItem.quantity = 1;
                        newWorkItem.children.push(newAhsItem);
                    });
                    
                    // Always add the work item, even if it has no AHS items
                    targetArray.push(newWorkItem);
                    newWorkItem.open = true; // Ensure it's open
                    
                    this.$nextTick(() => { 
                        this.initializeAllSelects(this.$el);
                    });
                },

                // Adds a blank Section (parent)
                addRootSection() { 
                    this.items.push(this.newItem(true, false)); // isParent: true, isWorkType: false
                },
                
                // Adds a blank Work Item (sub-parent)
                addWorkItem(parentItem) {
                    if (!parentItem.children) parentItem.children = [];
                    parentItem.children.push(this.newItem(true, false)); // isParent: true, isWorkType: false
                    parentItem.open = true;
                },
                
                // Adds a blank AHS Item (line item)
                addAHSItem(parentItem) {
                    if (!parentItem.children) parentItem.children = [];
                    parentItem.children.push(this.newItem(false, false)); // isParent: false, isWorkType: false
                    parentItem.open = true;
                    this.$nextTick(() => { 
                        this.initializeAllSelects(this.$el);
                    });
                },
                
                removeItem(itemToRemove) {
                    const findAndRemove = (items, targetId) => {
                        for (let i = 0; i < items.length; i++) {
                            if (items[i].id === targetId) {
                                this.destroyItemSelects(items[i]);
                                items.splice(i, 1);
                                return true;
                            }
                            if (items[i].children && items[i].children.length > 0) {
                                if (findAndRemove(items[i].children, targetId)) return true;
                            }
                        }
                        return false;
                    };
                    findAndRemove(this.items, itemToRemove.id);
                },
                
                newItem(isParent = false, isWorkType = false) { 
                    return { 
                        id: `temp_${Date.now()}_${Math.random()}`, 
                        unit_rate_analysis_id: null, 
                        description: '', 
                        item_code: '', 
                        uom: '', 
                        quantity: isParent ? null : 0, 
                        unit_price: isParent ? null : 0, 
                        children: [], 
                        open: true, 
                        isParent: isParent,
                        isWorkType: isWorkType
                    };
                },
                
                toggleChildren(item) {
                    item.open = !item.open;
                },
                
                linkAHS(item, event) {
                    const selectedOption = event.target.options[event.target.selectedIndex];
                    const selectedId = event.target.value;

                    if (selectedId && selectedOption.hasAttribute('data-cost')) {
                        // This is the correct way: read from the option's data attributes
                        item.description = selectedOption.getAttribute('data-name');
                        item.item_code = selectedOption.getAttribute('data-code');
                        item.uom = selectedOption.getAttribute('data-unit');
                        item.unit_price = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
                        item.unit_rate_analysis_id = selectedId;
                    } else {
                        // This is the "Select AHS Item" (blank) option
                        item.description = '';
                        item.item_code = '';
                        item.uom = '';
                        item.unit_price = 0;
                        item.unit_rate_analysis_id = null;
                    }
                },
                
                calculateItemTotal(item) {
                    if (item.isParent) {
                        item.quantity = null;
                        item.unit_price = null;
                        if (!item.children) item.children = [];
                        return item.children.reduce((sum, child) => sum + this.calculateItemTotal(child), 0);
                    } else {
                        if (item.quantity === null) item.quantity = 0;
                        return (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
                    }
                },
                
                get grandTotal() {
                    return this.items.reduce((sum, item) => sum + this.calculateItemTotal(item), 0);
                },
                
                formatCurrency(value) {
                    if (isNaN(value)) return 'Rp 0'; 
                    return parseFloat(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },
                
                initializeAllSelects(container) {
                    if (typeof TomSelect === 'undefined') return;
                    container.querySelectorAll('.ahs-select:not(.tomselected)').forEach(el => {
                        this.initializeSelects(el, true);
                    });
                    container.querySelectorAll('select:not(.ahs-select):not(.tomselected)').forEach(el => {
                        this.initializeSelects(el, false);
                    });
                },

                initializeSelects(element, useTomSelect) {
                    if (typeof TomSelect === 'undefined') return;
                    if (element && !element.tomselect) { 
                        if (useTomSelect) {
                            this.initSelectCounter++; 
                            if (!element.id) { element.id = `tomselect-ahs-${this.initSelectCounter}`; } 
                            new TomSelect(element, { create: false, sortField: { field: "text", direction: "asc" } }); 
                        } else {
                            new TomSelect(element, { create: false });
                        }
                    }
                },
                
                destroySelect(element) {
                    if (element && element.tomselect) { element.tomselect.destroy(); }
                },
                
                destroyItemSelects(item) {
                    if (item.children && item.children.length > 0) { 
                        item.children.forEach(child => this.destroyItemSelects(child)); 
                    }
                    let el = document.getElementById(`ahs_id_${item.id}`);
                    if (el) this.destroySelect(el);
                    
                    let el2 = document.getElementById(`desc_${item.id}`);
                    if (el2 && el2.nextElementSibling && el2.nextElementSibling.tomselect) {
                        this.destroySelect(el2.nextElementSibling);
                    }
                }
            }
        }
    </script>
@endpush
</x-app-layout>