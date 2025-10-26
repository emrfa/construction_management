<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; {{ $project->project_code }}
            </a>
            <span class="text-gray-500">/</span>
            <span>Create Material Request</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div x-data="materialRequestForm()">
                        <form method="POST" action="{{ route('material-requests.store') }}">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <input type="hidden" name="items_json" :value="JSON.stringify(items.map(item => ({ quotation_item_id: item.quotation_item_id, inventory_item_id: item.inventory_item_id, quantity_requested: item.quantity_requested })))">


                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div>
                                    <x-input-label for="request_date" :value="__('Request Date')" />
                                    <x-text-input id="request_date" class="block mt-1 w-full" type="date" name="request_date" :value="old('request_date', date('Y-m-d'))" required />
                                </div>
                                <div>
                                    <x-input-label for="required_date" :value="__('Required Date (Optional)')" />
                                    <x-text-input id="required_date" class="block mt-1 w-full" type="date" name="required_date" :value="old('required_date')" />
                                </div>
                                <div class="md:col-span-3">
                                    <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                    <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <hr class="my-6">

                            <h3 class="text-lg font-semibold mb-2">Requested Items (Based on RAB/WBS)</h3>
                            <div class="space-y-3">
                                <template x-for="(item, index) in items" :key="item.id">
                                    <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border">
                                        <div class="flex-1">
                                            <label :for="`wbs_id_${item.id}`" class="text-xs font-medium text-gray-700">WBS Task</label>
                                            <select :id="`wbs_id_${item.id}`"
                                                    class="wbs-select block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required
                                                    x-init="initializeWbsSelect($el, index)"
                                                    >
                                                <option value="">Select WBS Task...</option>
                                                {{-- Options added via JS --}}
                                            </select>
                                             <input type="hidden" :name="`items[${index}][quotation_item_id]`" x-model="item.quotation_item_id">
                                        </div>
                                        <div class="flex-1">
                                            <label :for="`mat_id_${item.id}`" class="text-xs font-medium text-gray-700">Material</label>
                                            <select :id="`mat_id_${item.id}`"
                                                    class="material-select block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required
                                                    :disabled="!item.quotation_item_id"
                                                    x-init="initializeMaterialSelect($el, index)"
                                                    >
                                                <option value="">Select Material...</option>
                                                {{-- Options added via JS --}}
                                            </select>
                                             <input type="hidden" :name="`items[${index}][inventory_item_id]`" x-model="item.inventory_item_id">
                                        </div>
                                        <div class="w-32">
                                            <label :for="`req_qty_${item.id}`" class="text-xs font-medium text-gray-700">Qty Requested</label>
                                            <input x-model.number="item.quantity_requested" :id="`req_qty_${item.id}`" type="number" step="0.01" min="0.01"
                                                   :name="`items[${index}][quantity_requested]`"
                                                   class="block mt-1 w-full border-gray-300 text-sm text-right rounded-md shadow-sm" required>
                                            <span class="text-xs text-gray-500" x-text="item.uom"></span>
                                        </div>
                                        <div class="pt-5">
                                            <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">✖</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addItem()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Add Item</button>

                            <div class="flex items-center justify-end mt-6 border-t pt-4">
                                <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md"> {{ __('Cancel') }} </a>
                                <x-primary-button class="ml-4"> {{ __('Submit Request') }} </x-primary-button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @php
        // Prepare WBS data (key: ID, value: description) for JS
        $wbsOptionsJs = collect($wbsMaterials)
                        ->map(fn($data, $id) => ['value' => $id, 'text' => $data['description']])
                        ->values() // Convert to indexed array for TomSelect
                        ->all();

        // Prepare old items array
        $oldInputItems = old('items');
        $oldItemsArray = is_array($oldInputItems) ? $oldInputItems : [];
        if (empty($oldItemsArray) && old('items_json')) {
             $oldItemsArray = json_decode(old('items_json'), true) ?? [];
        }
        // Ensure keys are reset if loading from old input which might have non-sequential keys
        $oldItemsArray = array_values($oldItemsArray);
         // Add initial default item ONLY if not loading from old()
        if (empty($oldItemsArray) && !request()->old()) {
            $oldItemsArray = [ [ 'id' => time() . '-init', 'quotation_item_id' => '', 'inventory_item_id' => '', 'quantity_requested' => 1, 'uom' => '' ] ];
        }
    @endphp

    <script>
        const wbsMaterialsData = @json($wbsMaterials); // Detailed data keyed by WBS ID
        const wbsOptionsJs = @json($wbsOptionsJs); // Simple array for WBS TomSelect options

        function materialRequestForm() {
            return {
                items: @json($oldItemsArray),
                wbsMaterialsData: wbsMaterialsData,
                wbsOptionsJs: wbsOptionsJs, // Store options array
                tomSelectInstances: {},

                addItem() {
                    let newItem = { id: Date.now() + '-' + Math.random().toString(36).substr(2, 5), quotation_item_id: '', inventory_item_id: '', quantity_requested: 1, uom: '' };
                    this.items.push(newItem);
                    // Let x-init handle initialization on the new elements
                },
                removeItem(index) {
                    let item = this.items[index];
                    if(item) {
                        this.destroySelect(`wbs_id_${item.id}`);
                        this.destroySelect(`mat_id_${item.id}`);
                    }
                    if (index >= 0 && index < this.items.length) {
                        this.items.splice(index, 1);
                    }
                },
                updateMaterialsOptions(materialSelectElement, wbsId, selectedMaterialId = null) {
                    let tomInstance = materialSelectElement ? this.tomSelectInstances[materialSelectElement.id] : null;
                    if (!tomInstance) { console.error("TomSelect instance not found for material select:", materialSelectElement?.id); return; }

                    let currentMaterialValue = selectedMaterialId ?? tomInstance.getValue(); // Preserve value if provided (e.g., from old())
                    tomInstance.clearOptions();
                    tomInstance.addOption({ value: '', text: 'Select Material...' });

                    if (wbsId && this.wbsMaterialsData[wbsId] && this.wbsMaterialsData[wbsId].materials) {
                        for (const materialId in this.wbsMaterialsData[wbsId].materials) {
                            const materialData = this.wbsMaterialsData[wbsId].materials[materialId];
                            tomInstance.addOption({
                                value: materialId,
                                text: `${materialData.code} - ${materialData.name} (${materialData.uom})`
                            });
                        }
                         tomInstance.enable();
                    } else {
                        tomInstance.disable();
                    }
                    // Restore previous selection if applicable, otherwise clear
                     if (wbsId && currentMaterialValue && this.wbsMaterialsData[wbsId]?.materials[currentMaterialValue]) {
                         tomInstance.setValue(currentMaterialValue, true); // Silent update
                     } else {
                         tomInstance.clear(true); // Silent clear
                     }
                },
                 updateUomDisplay(index, materialId) {
                    if (this.items[index]) {
                        let wbsId = this.items[index].quotation_item_id;
                        if(wbsId && materialId && this.wbsMaterialsData[wbsId]?.materials[materialId]) {
                             this.items[index].uom = this.wbsMaterialsData[wbsId].materials[materialId].uom;
                        } else {
                            this.items[index].uom = '';
                        }
                    }
                 },
                initializeWbsSelect(element, index) {
                    if (!element || this.tomSelectInstances[element.id]) return; // Skip if already initialized
                    let self = this;
                    let initialValue = this.items[index]?.quotation_item_id || ''; // Get initial value from Alpine model
                    let instance = new TomSelect(element, {
                        options: self.wbsOptionsJs, // Use pre-loaded options
                        placeholder: 'Select WBS Task...',
                        items: initialValue ? [initialValue] : [], // Set initial selected item for TomSelect
                        onChange: function(value) {
                             // Update Alpine model FIRST
                            if (self.items[index]) {
                                self.items[index].quotation_item_id = value;
                                self.items[index].inventory_item_id = '';
                                self.items[index].uom = '';
                            }
                            // Find the corresponding material select and update its options
                            let materialSelectElement = element.closest('.flex.items-center').querySelector('.material-select');
                            self.updateMaterialsOptions(materialSelectElement, value);
                        }
                    });
                    this.tomSelectInstances[element.id] = instance;
                },
                initializeMaterialSelect(element, index) {
                     if (!element || this.tomSelectInstances[element.id]) return; // Skip if already initialized
                     let self = this;
                     let initialValue = this.items[index]?.inventory_item_id || ''; // Get initial value
                     let wbsId = this.items[index]?.quotation_item_id;

                     let instance = new TomSelect(element, {
                         placeholder: 'Select Material...',
                         items: initialValue ? [initialValue] : [], // Set initial selected item
                         onChange: function(value) {
                             if (self.items[index]) {
                                 self.items[index].inventory_item_id = value;
                                 self.updateUomDisplay(index, value);
                             }
                         }
                     });
                     this.tomSelectInstances[element.id] = instance;
                     // Populate initial options based on current WBS ID
                     this.updateMaterialsOptions(element, wbsId, initialValue); // Pass initial value
                     if (!wbsId) instance.disable(); // Ensure disabled if no WBS initially
                },
                destroySelect(elementId) {
                     if (elementId && this.tomSelectInstances[elementId]) {
                         this.tomSelectInstances[elementId].destroy();
                         delete this.tomSelectInstances[elementId];
                     }
                 },
                init() {
                    // Alpine automatically initializes items from @json($oldItemsArray)
                    // x-init on the elements will call the initialize functions
                }
            }
        }
    </script>
</x-app-layout>