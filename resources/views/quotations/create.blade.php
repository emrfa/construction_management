<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Quotations', 'url' => route('quotations.index')],
            ['label' => 'New Quotation', 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Quotation') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Main Form Card --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl border border-gray-100">
                <div class="p-8 text-gray-900">

                    {{-- Alpine Data Scope --}}
                    <div x-data='rabBuilder(@json($ahsJsonData), @json($workTypesLibrary_json), @json($oldItemsArray), @json($workItemsLibrary_json))'>
                        <form method="POST" action="{{ route('quotations.store') }}">
                            @csrf
                            <input type="hidden" name="items_json" :value="JSON.stringify(items)">
                            <input type="hidden" name="overrides_json" :value="JSON.stringify(overrides)">

                            {{-- 1. HEADER DETAILS SECTION --}}
                            <div class="bg-gray-50 rounded-xl p-6 mb-8 border border-gray-200/60">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Project Details
                                    </h3>
                                    <button type="button" 
                                            @click="openPricelistModal()"
                                            class="inline-flex items-center px-3 py-1.5 bg-white border border-indigo-200 rounded-lg font-medium text-xs text-indigo-700 hover:bg-indigo-50 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Manage Prices
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    {{-- Client --}}
                                    <div class="space-y-1">
                                        <label for="client_id" class="block font-semibold text-xs text-gray-600 uppercase tracking-wider">{{ __('Client') }}</label>
                                        <select id="client_id" name="client_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" required>
                                            <option value="">Select a client...</option>
                                            @foreach ($clients as $client)
                                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Project Name --}}
                                    <div class="space-y-1">
                                        <label for="project_name" class="block font-semibold text-xs text-gray-600 uppercase tracking-wider">{{ __('Project Name') }}</label>
                                        <input id="project_name" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" type="text" name="project_name" value="{{ old('project_name') }}" placeholder="e.g. Villa Construction" required />
                                    </div>

                                    {{-- Location --}}
                                    <div class="space-y-1">
                                        <label for="location" class="block font-semibold text-xs text-gray-600 uppercase tracking-wider">{{ __('Location') }}</label>
                                        <input id="location" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Bali, Indonesia" />
                                    </div>

                                    {{-- Date --}}
                                    <div class="space-y-1">
                                        <label for="date" class="block font-semibold text-xs text-gray-600 uppercase tracking-wider">{{ __('Date') }}</label>
                                        <input id="date" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm" type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required />
                                    </div>
                                </div>
                            </div>
                            
                            {{-- 2. WBS ITEMS SECTION --}}
                            <div class="mb-8">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-gray-800">Quotation Items (WBS)</h3>
                                    <div class="text-xs text-gray-500 italic">
                                        Build your Bill of Quantities by adding sections and items below.
                                    </div>
                                </div>

                                <div class="border border-gray-200 rounded-lg">
                                    {{-- Table Header --}}
                                    <div class="grid grid-cols-12 gap-4 bg-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider px-4 py-3 border-b border-gray-200">
                                        <div class="col-span-5">Description / Item</div>
                                        <div class="col-span-1">Code</div>
                                        <div class="col-span-1">Unit</div>
                                        <div class="col-span-2 text-right">Quantity</div>
                                        <div class="col-span-1 text-right">Unit Price</div>
                                        <div class="col-span-2 text-right">Subtotal</div>
                                    </div>

                                    {{-- Recursive Item Template --}}
                                    <script type="text/template" x-ref="itemTemplate">
                                        <div class="group border-b border-gray-100 last:border-b-0 hover:bg-gray-50/50 transition-colors duration-150">
                                            
                                            {{-- Row Content --}}
                                            <div class="grid grid-cols-12 gap-4 items-center px-4 py-2" 
                                                 :class="{ 
                                                    'bg-indigo-50/50': item.type === 'sub_project',
                                                    'bg-gray-50/50': item.type === 'work_type'
                                                 }">
                                                
                                                {{-- Description Column --}}
                                                <div class="col-span-5 flex items-center">
                                                    {{-- Indentation Spacer --}}
                                                    <div :style="`width: ${level * 20}px`" class="flex-shrink-0"></div>

                                                    {{-- Expand/Collapse Toggle --}}
                                                    <button type="button" @click="toggleChildren(item)"
                                                            class="text-gray-400 hover:text-indigo-600 w-6 h-6 flex items-center justify-center mr-1 flex-shrink-0 transition-colors"
                                                            x-show="item.isParent">
                                                        <svg x-show="!item.open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                        <svg x-show="item.open" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                                    </button>
                                                    <div class="w-6 mr-1" x-show="!item.isParent"></div> {{-- Spacer for leaf nodes --}}

                                                    <div class="flex-1">
                                                        {{-- Leaf Node: AHS Selector --}}
                                                        <template x-if="!item.isParent">
                                                            <div class="relative">
                                                                <select x-model="item.unit_rate_analysis_id"
                                                                        @change="linkAHS(item, $event)"
                                                                        :id="`ahs_id_${item.id}`"
                                                                        class="ahs-select block w-full border-0 bg-transparent py-1.5 pl-0 pr-8 text-gray-900 sm:text-sm sm:leading-6"
                                                                        x-init="$nextTick(() => initializeSelects($el, true))">
                                                                    <option value="">Select AHS Item...</option>
                                                                    @foreach ($ahsLibrary as $ahs)
                                                                        <option value="{{ $ahs->id }}" data-code="{{ $ahs->code }}" data-name="{{ $ahs->name }}" data-unit="{{ $ahs->unit }}" data-cost="{{ $ahs->total_cost }}">{{ $ahs->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <input type="hidden" x-model="item.description" />
                                                            </div>
                                                        </template>

                                                        {{-- Parent Node: Title Input --}}
                                                        <template x-if="item.isParent">
                                                            <input x-model="item.description" type="text"
                                                                class="block w-full border-0 border-b border-transparent bg-transparent py-1 text-gray-900 placeholder:text-gray-400 focus:border-indigo-600 focus:ring-0 sm:text-sm font-semibold transition-all"
                                                                :class="{'text-base text-indigo-900': item.type === 'sub_project', 'text-sm text-gray-800': item.type !== 'sub_project'}"
                                                                placeholder="Section Title" required>
                                                        </template>
                                                    </div>
                                                </div>

                                                {{-- Code --}}
                                                <div class="col-span-1">
                                                    <input x-model="item.item_code" type="text"
                                                        class="block w-full border-0 bg-transparent py-1.5 text-gray-500 text-xs focus:ring-0 italic"
                                                        placeholder="-" readonly disabled
                                                        x-show="!item.isParent">
                                                </div>

                                                {{-- Unit --}}
                                                <div class="col-span-1">
                                                    <input x-model="item.uom" type="text"
                                                        class="block w-full border-0 bg-transparent py-1.5 text-gray-900 text-sm focus:ring-0 text-center"
                                                        placeholder="-" :disabled="item.isParent || item.unit_rate_analysis_id"
                                                        x-show="!item.isParent">
                                                </div>

                                                {{-- Quantity --}}
                                                <div class="col-span-2">
                                                    <input x-model.number="item.quantity" type="number" step="0.01" 
                                                        class="block w-full border-0 bg-gray-50/50 py-1.5 text-gray-900 text-sm text-right focus:ring-1 focus:ring-indigo-500 rounded-md transition-colors" 
                                                        placeholder="0" :disabled="item.isParent"
                                                        x-show="!item.isParent">
                                                </div>

                                                {{-- Unit Price --}}
                                                <div class="col-span-1">
                                                    <input x-model.number="item.unit_price" type="number" step="0.01"
                                                        class="block w-full border-0 bg-transparent py-1.5 text-gray-900 text-sm text-right focus:ring-0" 
                                                        placeholder="0" :disabled="item.isParent || item.unit_rate_analysis_id"
                                                        x-show="!item.isParent"
                                                        :class="{'text-gray-500': item.unit_rate_analysis_id}">
                                                </div>

                                                {{-- Subtotal --}}
                                                <div class="col-span-2 text-right font-medium text-sm text-gray-900">
                                                    <span x-text="formatCurrency(calculateItemTotal(item))"></span>
                                                </div>
                                            </div>

                                            {{-- Action Toolbar (Hover only, or always visible for parents) --}}
                                            <div class="px-4 py-1 bg-gray-50 border-t border-gray-100 flex items-center gap-3 text-xs">
                                                
                                                {{-- Add Buttons based on Type --}}
                                                <template x-if="item.isParent">
                                                    <div class="flex items-center gap-2">
                                                        
                                                        {{-- Level 0: Sub-Project --}}
                                                        <template x-if="item.type === 'sub_project'">
                                                            <div class="flex items-center gap-2">
                                                                <select @change="addWorkType($event.target.value, item.children); $event.target.tomselect.clear();"
                                                                    class="w-40"
                                                                    x-init="initializeSelects($el, false)">
                                                                    <option value="">+ Add Work Type...</option>
                                                                    @foreach($workTypesLibrary_json as $type)
                                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" @click="addWorkItem(item)" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                                    Sub-Section
                                                                </button>
                                                            </div>
                                                        </template>

                                                        {{-- Level 1: Work Type --}}
                                                        <template x-if="item.type === 'work_type'">
                                                            <div class="flex items-center gap-2">
                                                                <select @change="addWorkItemFromLibrary($event.target.value, item.children); $event.target.tomselect.clear();"
                                                                    class="w-48"
                                                                    x-init="initializeSelects($el, false)">
                                                                    <option value="">+ Add Work Item from Library...</option>
                                                                    @foreach($workItemsLibrary_json as $workItem)
                                                                        <option value="{{ $workItem->id }}">{{ $workItem->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" @click="addWorkItem(item)" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                                    Manual Item
                                                                </button>
                                                                <span class="text-gray-300">|</span>
                                                                <button type="button" @click="addAHSItem(item)" class="text-emerald-600 hover:text-emerald-800 font-medium flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                                    AHS
                                                                </button>
                                                            </div>
                                                        </template>

                                                        {{-- Level 2: Work Item --}}
                                                        <template x-if="item.type === 'work_item'">
                                                            <button type="button" @click="addAHSItem(item)" class="text-emerald-600 hover:text-emerald-800 font-medium flex items-center gap-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                                Add AHS
                                                            </button>
                                                        </template>
                                                    </div>
                                                </template>

                                                <div class="flex-1"></div> {{-- Spacer --}}

                                                {{-- Delete Button --}}
                                                <button type="button" @click="removeItem(item)" class="text-red-500 hover:text-red-700 flex items-center gap-1 transition-colors">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    Remove
                                                </button>
                                            </div>

                                            {{-- Recursive Children --}}
                                            <div class="bg-white" x-show="item.open && item.children.length > 0" x-transition>
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

                                    {{-- Root Items Render --}}
                                    <div id="root-items" class="bg-white min-h-[100px]">
                                        <template x-for="(item, index) in items" :key="item.id">
                                            <div x-html="$refs.itemTemplate.innerHTML" x-data="{ 
                                                    item: item, 
                                                    index: index, 
                                                    parentArray: items, 
                                                    level: 0,
                                                    isWorkType: item.isWorkType || false
                                                }"></div>
                                        </template>
                                        
                                        {{-- Empty State --}}
                                        <div x-show="items.length === 0" class="p-8 text-center text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            <p>No items added yet. Start by adding a section below.</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Root Actions --}}
                                <div class="mt-4 flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                    <div class="w-64">
                                        <select @change="addWorkType($event.target.value, items); $event.target.tomselect.clear();"
                                            class="w-full"
                                            x-init="initializeSelects($el, false)">
                                            <option value="">+ Quick Add Work Type...</option>
                                            @foreach($workTypesLibrary_json as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button type="button" @click="addRootSection()" 
                                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                        Add New Section
                                    </button>
                                </div>
                            </div>

                            {{-- 3. FOOTER & TOTALS --}}
                            <div class="border-t border-gray-200 pt-8 mt-8">
                                <div class="flex flex-col md:flex-row justify-end items-center gap-8">
                                    <div class="text-right">
                                        <span class="block text-sm text-gray-500 mb-1">Total Estimate</span>
                                        <span class="block text-4xl font-black text-indigo-600 tracking-tight" x-text="formatCurrency(grandTotal)"></span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end mt-8 gap-4">
                                    <a href="{{ route('quotations.index') }}" class="inline-flex items-center px-6 py-3 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Cancel
                                    </a>
                                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest shadow-sm hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Save Quotation
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- Pricelist Modal (Unchanged logic, just styling tweaks if needed) --}}
                        <x-modal name="pricelist-modal" focusable>
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-gray-900 mb-2">Project Price List</h2>
                                <p class="text-sm text-gray-500 mb-6">
                                    Override global prices for this specific project. Changes here affect all matching items in this quotation.
                                </p>

                                <div class="border-b border-gray-200 mb-6">
                                    <nav class="-mb-px flex space-x-8">
                                        <button @click="activeTab = 'material'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'material', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'material'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Materials</button>
                                        <button @click="activeTab = 'labor'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'labor', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'labor'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Labor</button>
                                        <button @click="activeTab = 'equipment'" :class="{'border-indigo-500 text-indigo-600': activeTab === 'equipment', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'equipment'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">Equipment</button>
                                    </nav>
                                </div>

                                <div x-show="isLoading" class="text-center py-12">
                                    <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <p class="mt-2 text-sm text-gray-500">Loading resources...</p>
                                </div>

                                {{-- Materials Tab --}}
                                <div x-show="!isLoading && activeTab === 'material'" class="overflow-y-auto max-h-[400px] border rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 sticky top-0"><tr><th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item Name</th><th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Default Price</th><th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Project Price</th></tr></thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="item in resources.materials" :key="item.id">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-sm text-gray-900">
                                                        <div class="font-medium" x-text="item.name"></div>
                                                        <div class="text-xs text-gray-500" x-text="item.code"></div>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-right text-gray-500" x-text="formatCurrency(item.default_price)"></td>
                                                    <td class="px-4 py-3 text-right">
                                                        <input type="number" step="0.01" 
                                                            x-model="overrides.material[item.id]" 
                                                            :placeholder="item.default_price"
                                                            class="w-32 text-right text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="resources.materials.length === 0"><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">No materials found in this quotation.</td></tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Labor Tab --}}
                                <div x-show="!isLoading && activeTab === 'labor'" class="overflow-y-auto max-h-[400px] border rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 sticky top-0"><tr><th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Labor Type</th><th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Default Rate</th><th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Project Rate</th></tr></thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="item in resources.labors" :key="item.id">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-sm text-gray-900" x-text="item.name"></td>
                                                    <td class="px-4 py-3 text-sm text-right text-gray-500" x-text="formatCurrency(item.default_price)"></td>
                                                    <td class="px-4 py-3 text-right">
                                                        <input type="number" step="0.01" x-model="overrides.labor[item.id]" :placeholder="item.default_price" class="w-32 text-right text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="resources.labors.length === 0"><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">No labor items found.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                {{-- Equipment Tab --}}
                                <div x-show="!isLoading && activeTab === 'equipment'" class="overflow-y-auto max-h-[400px] border rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 sticky top-0"><tr><th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Equipment</th><th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Default Rate</th><th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Project Rate</th></tr></thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <template x-for="item in resources.equipments" :key="item.id">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 text-sm text-gray-900" x-text="item.name"></td>
                                                    <td class="px-4 py-3 text-sm text-right text-gray-500" x-text="formatCurrency(item.default_price)"></td>
                                                    <td class="px-4 py-3 text-right">
                                                        <input type="number" step="0.01" x-model="overrides.equipment[item.id]" :placeholder="item.default_price" class="w-32 text-right text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                    </td>
                                                </tr>
                                            </template>
                                            <tr x-show="resources.equipments.length === 0"><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">No equipment found.</td></tr>
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
    <style>
        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
        }

        /* Firefox */
        input[type=number] {
        -moz-appearance: textfield;
        }
    </style>
    <script>
         document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[type=number]').forEach(input => {
            input.addEventListener('wheel', (e) => {
                e.preventDefault();
            });
        });
    });
    window.rabBuilder = function(ahsLibraryData, workTypesLibrary, oldItems, workItemsLibrary) {
        return {
            items: oldItems || [],
            ahsData: ahsLibraryData,
            library: {
                workTypes: workTypesLibrary,
                workItems: workItemsLibrary
            },
            initSelectCounter: 0,
            activeTab: 'material',
            isLoading: false,
            resources: { materials: [], labors: [], equipments: [] },
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

            async openPricelistModal() {
                this.$dispatch('open-modal', 'pricelist-modal');
                this.isLoading = true;
                
                let ahsIds = [];
                const collectAhs = (arr) => {
                    arr.forEach(item => {
                        if (item.unit_rate_analysis_id) ahsIds.push(item.unit_rate_analysis_id);
                        if (item.children) collectAhs(item.children);
                    });
                };
                collectAhs(this.items);
                ahsIds = [...new Set(ahsIds)];

                if (ahsIds.length === 0) {
                    this.resources = { materials: [], labors: [], equipments: [] };
                    this.isLoading = false;
                    return;
                }

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
                
                let ahsIds = [];
                const collectAhs = (arr) => {
                    arr.forEach(item => {
                        if (item.unit_rate_analysis_id) ahsIds.push(item.unit_rate_analysis_id);
                        if (item.children) collectAhs(item.children);
                    });
                };
                collectAhs(this.items);
                ahsIds = [...new Set(ahsIds)];

                try {
                    const response = await axios.post('{{ route('api.quotation.recalculate') }}', {
                        ahs_ids: ahsIds,
                        overrides: this.overrides
                    });
                    
                    const newPrices = response.data;

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
                } catch (error) {
                    console.error(error);
                    alert('Failed to recalculate prices.');
                } finally {
                    this.isLoading = false;
                }
            },

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

                if (parentItem.type !== 'work_type' && parentItem.type !== 'sub_project') {
                     if (parentItem.type === 'sub_project') {
                         // Adding a "Sub-Section" (generic parent)
                         parentItem.children.push(this.newItem(true, false, 'work_type')); // Treat as work type container
                     } else if (parentItem.type === 'work_type') {
                         parentItem.children.push(this.newItem(true, false, 'work_item'));
                     } else {
                        alert('Work Items can only be added under a Work Type.');
                        return;
                     }
                } else {
                     if (parentItem.type === 'work_type') {
                         parentItem.children.push(this.newItem(true, false, 'work_item'));
                     } else {
                         // sub_project
                         parentItem.children.push(this.newItem(true, true, 'work_type')); 
                     }
                }
                parentItem.open = true;
            },

            addWorkItemFromLibrary(workItemId, targetArray, parentType = null) {
                if (!workItemId) return;
                
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

            newItem(isParent = false, isWorkType = false, type = null) {
                return {
                    id: `temp_${Date.now()}_${Math.random()}`,
                    type: type,
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