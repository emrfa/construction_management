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
                            <input type="hidden" name="overrides_json" :value="JSON.stringify(overrides)">

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
                                    <label for="location" class="block font-medium text-sm text-gray-700">{{ __('Location') }}</label>
                                    <input id="location" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="location" value="{{ old('location') }}" />
                                </div>
                                <div>
                                    <label for="date" class="block font-medium text-sm text-gray-700">{{ __('Date') }}</label>
                                    <input id="date" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required />
                                </div>
                                <div class="flex justify-end mb-4">
                                    <button type="button" 
                                            @click="openPricelistModal()"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Manage Project Prices
                                    </button>
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

                                                        {{-- FIX 3: This is for WORK TYPES --}}
                                                        <template x-if="item.type === 'work_type'">
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
                        <x-modal name="pricelist-modal" focusable>
                            <div class="p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Project Price List (Global Override)</h2>
                                <p class="text-sm text-gray-600 mb-4">
                                    Adjusting a price here will update <strong>all</strong> AHS items in this quotation that use this resource.
                                </p>

                                <div class="border-b border-gray-200 mb-4">
                                    <nav class="-mb-px flex space-x-8">
                                        <button @click="activeTab = 'material'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'material', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'material'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Materials</button>
                                        <button @click="activeTab = 'labor'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'labor', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'labor'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Labor</button>
                                        <button @click="activeTab = 'equipment'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'equipment', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'equipment'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Equipment</button>
                                    </nav>
                                </div>

                                <div x-show="isLoading" class="text-center py-8">
                                    <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <p class="mt-2 text-sm text-gray-500">Loading resources...</p>
                                </div>

                                <div x-show="!isLoading && activeTab === 'material'" class="overflow-y-auto max-h-96">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Default Price</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Project Price</th></tr></thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="item in resources.materials" :key="item.id">
                                                <tr>
                                                    <td class="px-3 py-2 text-sm text-gray-900">
                                                        <div class="font-medium" x-text="item.name"></div>
                                                        <div class="text-xs text-gray-500" x-text="item.code"></div>
                                                    </td>
                                                    <td class="px-3 py-2 text-sm text-right text-gray-500" x-text="formatCurrency(item.default_price)"></td>
                                                    <td class="px-3 py-2 text-right">
                                                        <input type="number" step="0.01" 
                                                            x-model="overrides.material[item.id]" 
                                                            :placeholder="item.default_price"
                                                            class="w-32 text-right text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="resources.materials.length === 0"><td colspan="3" class="px-3 py-4 text-center text-sm text-gray-500">No materials found in this quotation.</td></tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div x-show="!isLoading && activeTab === 'labor'" class="overflow-y-auto max-h-96">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Labor Type</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Default Rate</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Project Rate</th></tr></thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="item in resources.labors" :key="item.id">
                                                <tr>
                                                    <td class="px-3 py-2 text-sm text-gray-900" x-text="item.name"></td>
                                                    <td class="px-3 py-2 text-sm text-right text-gray-500" x-text="formatCurrency(item.default_price)"></td>
                                                    <td class="px-3 py-2 text-right">
                                                        <input type="number" step="0.01" x-model="overrides.labor[item.id]" :placeholder="item.default_price" class="w-32 text-right text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="resources.labors.length === 0"><td colspan="3" class="px-3 py-4 text-center text-sm text-gray-500">No labor items found.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div x-show="!isLoading && activeTab === 'equipment'" class="overflow-y-auto max-h-96">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50"><tr><th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Equipment</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Default Rate</th><th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Project Rate</th></tr></thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="item in resources.equipments" :key="item.id">
                                                <tr>
                                                    <td class="px-3 py-2 text-sm text-gray-900" x-text="item.name"></td>
                                                    <td class="px-3 py-2 text-sm text-right text-gray-500" x-text="formatCurrency(item.default_price)"></td>
                                                    <td class="px-3 py-2 text-right">
                                                        <input type="number" step="0.01" x-model="overrides.equipment[item.id]" :placeholder="item.default_price" class="w-32 text-right text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="resources.equipments.length === 0"><td colspan="3" class="px-3 py-4 text-center text-sm text-gray-500">No equipment found.</td></tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-6 flex justify-end space-x-3">
                                    <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                                    <x-primary-button x-on:click="applyPricelist()">Apply & Recalculate</x-primary-button>
                                </div>
                            </div>
                        </x-modal>
                    </div> 
                </div>
            </div>
        </div>
    </div>

    @push('head-scripts')
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

            // --- NEW PROPERTIES (MOVED INSIDE) ---
            activeTab: 'material',
            isLoading: false,
            resources: { materials: [], labors: [], equipments: [] },
            // The Overrides Object (Stores user input)
            overrides: { material: {}, labor: {}, equipment: {} },

            init() {
                if (typeof TomSelect === 'undefined') {
                    console.warn('TomSelect not loaded. Select dropdowns will not be enhanced.');
                }

                if (this.items.length === 0) {
                    this.items = [];
                } else {
                    const normalize = (arr, parentType = null) => {
                        arr.forEach(item => {
                            item.parentType = parentType;
                            if (item.children && item.children.length) {
                                normalize(item.children, item.type || null);
                            }
                        });
                    };
                    normalize(this.items, null);
                }

                this.$nextTick(() => {
                    this.initializeAllSelects(document);
                });
            },

            // --- NEW METHODS (MOVED INSIDE) ---

            async openPricelistModal() {
                this.$dispatch('open-modal', 'pricelist-modal');
                this.isLoading = true;
                
                // 1. Collect all AHS IDs from the current items list
                let ahsIds = [];
                const collectAhs = (arr) => {
                    arr.forEach(item => {
                        if (item.unit_rate_analysis_id) ahsIds.push(item.unit_rate_analysis_id);
                        if (item.children) collectAhs(item.children);
                    });
                };
                collectAhs(this.items);
                ahsIds = [...new Set(ahsIds)]; // Unique IDs only

                if (ahsIds.length === 0) {
                    this.resources = { materials: [], labors: [], equipments: [] };
                    this.isLoading = false;
                    return;
                }

                // 2. Fetch components from API
                try {
                    const response = await axios.post('{{ route('api.quotation.resources') }}', { ahs_ids: ahsIds });
                    this.resources = response.data;
                } catch (error) {
                    console.error(error);
                    alert('Failed to load project resources.');
                } finally {
                    this.isLoading = false;
                }
            },

            async applyPricelist() {
                this.isLoading = true;
                
                // 1. Collect AHS IDs again
                let ahsIds = [];
                const collectAhs = (arr) => {
                    arr.forEach(item => {
                        if (item.unit_rate_analysis_id) ahsIds.push(item.unit_rate_analysis_id);
                        if (item.children) collectAhs(item.children);
                    });
                };
                collectAhs(this.items);
                ahsIds = [...new Set(ahsIds)];

                // 2. Send overrides to API to get recalculated AHS unit prices
                try {
                    const response = await axios.post('{{ route('api.quotation.recalculate') }}', {
                        ahs_ids: ahsIds,
                        overrides: this.overrides
                    });
                    
                    const newPrices = response.data; // Object: { ahs_id: new_price }

                    // 3. Update the frontend Items recursively
                    const updateItemPrices = (arr) => {
                        arr.forEach(item => {
                            if (item.unit_rate_analysis_id && newPrices[item.unit_rate_analysis_id] !== undefined) {
                                item.unit_price = parseFloat(newPrices[item.unit_rate_analysis_id]);
                            }
                            if (item.children) updateItemPrices(item.children);
                        });
                    };
                    updateItemPrices(this.items);

                    this.$dispatch('close-modal', 'pricelist-modal');
                    // Notify user visually if you want
                } catch (error) {
                    console.error(error);
                    alert('Failed to recalculate prices.');
                } finally {
                    this.isLoading = false;
                }
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
                const selected = event.target.options[event.target.selectedIndex];
                const id = event.target.value;
                if (id && selected?.dataset.cost) {
                    item.description = selected.dataset.name;
                    item.item_code = selected.dataset.code;
                    item.uom = selected.dataset.unit;
                    item.unit_price = parseFloat(selected.dataset.cost) || 0;
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