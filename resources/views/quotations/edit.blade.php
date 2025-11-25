<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- CHANGED --}}
            {{ __('Edit Quotation (RAB / BOQ)') }}: {{ $quotation->quotation_no }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Data call is identical, as controller prepares $oldItemsArray --}}
                    <div x-data='rabBuilder(@json($ahsJsonData), @json($workTypesLibrary_json), @json($oldItemsArray), @json($workItemsLibrary_json))'>
                        
                        {{-- CHANGED: Form action and added method --}}
                        <form method="POST" action="{{ route('quotations.update', $quotation) }}">
                            @csrf
                            @method('PATCH')
                            {{-- END CHANGED --}}

                            <input type="hidden" name="items_json" :value="JSON.stringify(items)">

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                <div>
                                    <label for="client_id" class="block font-medium text-sm text-gray-700">{{ __('Client') }}</label>
                                    <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="">Select a client</option>
                                        @foreach ($clients as $client)
                                            {{-- CHANGED: Pre-fill from $quotation --}}
                                            <option value="{{ $client->id }}" {{ old('client_id', $quotation->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="project_name" class="block font-medium text-sm text-gray-700">{{ __('Project Name') }}</label>
                                    {{-- CHANGED: Pre-fill from $quotation --}}
                                    <input id="project_name" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="project_name" value="{{ old('project_name', $quotation->project_name) }}" required />
                                </div>
                                <div>
                                    <label for="location" class="block font-medium text-sm text-gray-700">{{ __('Location') }}</label>
                                    <input id="location" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="location" value="{{ old('location', $quotation->location) }}" />
                                </div>
                                <div>
                                    <label for="sub_project_name" class="block font-medium text-sm text-gray-700">{{ __('Sub Project (Optional)') }}</label>
                                    {{-- CHANGED: Pre-fill from $quotation --}}
                                    <input id="sub_project_name" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="sub_project_name" value="{{ old('sub_project_name', $quotation->sub_project_name ?? '') }}" />
                                </div>
                                <div>
                                    <label for="date" class="block font-medium text-sm text-gray-700">{{ __('Date') }}</label>
                                    {{-- CHANGED: Pre-fill from $quotation --}}
                                    <input id="date" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="date" name="date" value="{{ old('date', $quotation->date->format('Y-m-d')) }}" required />
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

                                {{-- This template is identical to create.blade.php --}}
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
                                                        <template x-if="item.type === 'sub_project'">
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

                                                        {{-- This is for WORK TYPES --}}
                                                        <template x-if="item.type === 'work_type'">
                                                            <select @change="addWorkItemFromLibrary($event.target.value, item.children, item.type); $event.target.tomselect.clear();"
                                                                class="block text-xs border-gray-300 rounded-md shadow-sm py-1"
                                                                x-init="initializeSelects($el, false)">
                                                                <option value="">+ Add Work Item from Library...</option>
                                                                @foreach($workItemsLibrary_json as $workItem)
                                                                    <option value="{{ $workItem->id }}">{{ $workItem->name }}</option>
                                                                @endforeach
                                                            </select>
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
                                                    isWorkType: childItem.isWorkType || false,
                                                    type: childItem.type || null
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
                                                isWorkType: item.isWorkType || false,
                                                type: item.type || null
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
                                    
                                    {{-- CHANGED: Button text --}}
                                    <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"> {{ __('Update Quotation') }} </button>
                                </div>
                            </form>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('head-scripts')
    {{-- This entire script block is identical to create.blade.php --}}
    <script>
    window.rabBuilder = function(ahsLibraryData, workTypesLibrary, oldItems, workItemsLibrary) {
        return {
            items: oldItems || [],
            ahsData: ahsLibraryData,
            library: {
                workTypes: workTypesLibrary,
                workItems: workItemsLibrary
            },
            initSelectCounter: 0,

            init() {
                if (typeof TomSelect === 'undefined') {
                    console.warn('TomSelect not loaded. Select dropdowns will not be enhanced.');
                }

                if (this.items.length === 0) {
                    this.items = [];
                } else {
                    const normalize = (arr, parentType = null) => {
                        arr.forEach(item => {
                            // Ensure properties exist
                            item.isParent = !!item.isParent;
                            item.isWorkType = !!item.isWorkType;
                            item.type = item.type || (item.isParent ? (item.isWorkType ? 'work_type' : 'sub_project') : 'ahs');
                            item.open = item.open === undefined ? true : item.open; // Default open
                            item.parentType = parentType;
                            
                            if (item.children && item.children.length) {
                                normalize(item.children, item.type || null);
                            } else {
                                item.children = []; // Ensure children is always an array
                            }
                        });
                    };
                    normalize(this.items, null);
                }

                this.$nextTick(() => {
                    this.initializeAllSelects(document);
                });
            },

            /** ------------------------------
             * ADD FUNCTIONS
             * ------------------------------ */
            addRootSection() {
                this.items.push(this.newItem(true, false, 'sub_project'));
            },

            addWorkType(workTypeId, targetArray, parentType = null) {
                if (!workTypeId) return;
                if (parentType && !['sub_project', null].includes(parentType)) {
                    alert('Work Types can only be added under root or Sub Project.');
                    return;
                }

                const workType = this.library.workTypes.find(wt => wt.id == workTypeId);
                if (!workType) return;

                let newWorkTypeItem = this.newItem(true, true, 'work_type');
                newWorkTypeItem.description = workType.name;

                // Add direct AHS links (if any)
                if (workType.unit_rate_analyses?.length > 0) {
                    workType.unit_rate_analyses.forEach(ahs => {
                        let newAhsItem = this.newItem(false, false, 'ahs');
                        Object.assign(newAhsItem, {
                            unit_rate_analysis_id: ahs.id,
                            description: ahs.name,
                            item_code: ahs.code,
                            uom: ahs.unit,
                            unit_price: parseFloat(ahs.total_cost) || 0,
                            quantity: 1
                        });
                        newWorkTypeItem.children.push(newAhsItem);
                    });
                }

                // Add Work Items
                if (workType.work_items?.length > 0) {
                    workType.work_items.forEach(workItem => {
                        let newWorkItem = this.newItem(true, false, 'work_item');
                        newWorkItem.description = workItem.name;

                        if (workItem.unit_rate_analyses?.length > 0) {
                            workItem.unit_rate_analyses.forEach(ahs => {
                                let newAhsItem = this.newItem(false, false, 'ahs');
                                Object.assign(newAhsItem, {
                                    unit_rate_analysis_id: ahs.id,
                                    description: ahs.name,
                                    item_code: ahs.code,
                                    uom: ahs.unit,
                                    unit_price: parseFloat(ahs.total_cost) || 0,
                                    quantity: 1
                                });
                                newWorkItem.children.push(newAhsItem);
                            });
                        }

                        newWorkTypeItem.children.push(newWorkItem);
                    });
                }

                targetArray.push(newWorkTypeItem);
                this.$nextTick(() => this.initializeAllSelects(this.$el));
            },

            addWorkItem(parentItem) {
                if (parentItem.type === 'ahs') {
                    alert('Cannot add Work Item under AHS.');
                    return;
                }

                if (parentItem.type !== 'work_type') {
                    alert('Work Items can only be added under a Work Type.');
                    return;
                }

                parentItem.children.push(this.newItem(true, false, 'work_item'));
                parentItem.open = true;
            },

            addWorkItemFromLibrary(workItemId, targetArray, parentType = null) {
                if (!workItemId) return;
                if (parentType && parentType !== 'work_type') {
                    alert('Work Items can only be added under a Work Type.');
                    return;
                }

                const workItem = this.library.workItems.find(wi => wi.id == workItemId);
                if (!workItem) return;

                let newWorkItem = this.newItem(true, false, 'work_item');
                newWorkItem.description = workItem.name;

                if (workItem.unit_rate_analyses?.length > 0) {
                    workItem.unit_rate_analyses.forEach(ahs => {
                        let newAhsItem = this.newItem(false, false, 'ahs');
                        Object.assign(newAhsItem, {
                            unit_rate_analysis_id: ahs.id,
                            description: ahs.name,
                            item_code: ahs.code,
                            uom: ahs.unit,
                            unit_price: parseFloat(ahs.total_cost) || 0,
                            quantity: 1
                        });
                        newWorkItem.children.push(newAhsItem);
                    });
                }

                targetArray.push(newWorkItem);
                newWorkItem.open = true;
                this.$nextTick(() => this.initializeAllSelects(this.$el));
            },

            addAHSItem(parentItem) {
                if (!['sub_project', 'work_type', 'work_item', null].includes(parentItem.type)) {
                    alert('AHS can only be added under Work Type, Work Item, or top-level Section.');
                    return;
                }
                if (!parentItem.children) parentItem.children = [];
                parentItem.children.push(this.newItem(false, false, 'ahs'));
                parentItem.open = true;
                this.$nextTick(() => this.initializeAllSelects(this.$el));
            },

            /** ------------------------------
             * REMOVE + TOGGLE
             * ------------------------------ */
            removeItem(itemToRemove) {
                const findAndRemove = (items, targetId) => {
                    for (let i = 0; i < items.length; i++) {
                        if (items[i].id === targetId) {
                            this.destroyItemSelects(items[i]);
                            items.splice(i, 1);
                            return true;
                        }
                        if (items[i].children?.length) {
                            if (findAndRemove(items[i].children, targetId)) return true;
                        }
                    }
                    return false;
                };
                findAndRemove(this.items, itemToRemove.id);
            },

            toggleChildren(item) {
                item.open = !item.open;
            },

            /** ------------------------------
             * UTILITIES
             * ------------------------------ */
            newItem(isParent = false, isWorkType = false, type = null) {
                return {
                    id: `temp_${Date.now()}_${Math.random()}`,
                    type: type, // 'sub_project', 'work_type', 'work_item', 'ahs'
                    unit_rate_analysis_id: null,
                    description: '',
                    item_code: '',
                    uom: '',
                    quantity: isParent ? null : 0,
                    unit_price: isParent ? null : 0,
                    children: [],
                    open: true,
                    isParent,
                    isWorkType
                };
            },

            linkAHS(item, event) {
                const id = event.target.value;
                if (id && this.ahsData[id]) {
                    const ahsData = this.ahsData[id];
                    item.description = ahsData.name;
                    item.item_code = ahsData.code;
                    item.uom = ahsData.uom;
                    item.unit_price = parseFloat(ahsData.unit_price) || 0;
                    item.unit_rate_analysis_id = id;
                } else {
                    Object.assign(item, {
                        description: '',
                        item_code: '',
                        uom: '',
                        unit_price: 0,
                        unit_rate_analysis_id: null
                    });
                }
            },

            calculateItemTotal(item) {
                if (item.isParent) {
                    return (item.children || []).reduce((sum, c) => sum + this.calculateItemTotal(c), 0);
                }
                return (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
            },

            get grandTotal() {
                return this.items.reduce((sum, item) => sum + this.calculateItemTotal(item), 0);
            },

            formatCurrency(val) {
                if (isNaN(val)) return 'Rp 0';
                return parseFloat(val).toLocaleString('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            },

            /** ------------------------------
             * SELECT INIT + CLEANUP
             * ------------------------------ */
            initializeAllSelects(container) {
                if (typeof TomSelect === 'undefined') return;
                container.querySelectorAll('.ahs-select:not(.tomselected)').forEach(el => {
                    this.initializeSelects(el, true);
                });
                container.querySelectorAll('select:not(.ahs-select):not(.tomselected)').forEach(el => {
                    this.initializeSelects(el, false);
                });
            },

            initializeSelects(el, useTomSelect) {
                if (typeof TomSelect === 'undefined') return;
                if (el && !el.tomselect) {
                    if (useTomSelect) {
                        this.initSelectCounter++;
                        if (!el.id) el.id = `tomselect-${this.initSelectCounter}`;
                        new TomSelect(el, { create: false, sortField: { field: "text", direction: "asc" } });
                    } else {
                        new TomSelect(el, { create: false });
                    }
                }
            },

            destroySelect(el) {
                if (el?.tomselect) el.tomselect.destroy();
            },

            destroyItemSelects(item) {
                (item.children || []).forEach(child => this.destroyItemSelects(child));
                const el = document.getElementById(`ahs_id_${item.id}`);
                if (el) this.destroySelect(el);
            }
        };
    };
    </script>
    @endpush
</x-app-layout>