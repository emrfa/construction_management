<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New AHS Definition') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div x-data="ahsForm()">
                        <form method="POST" action="{{ route('ahs-library.store') }}">
                            @csrf

                            <h3 class="text-lg font-semibold border-b pb-2 mb-4">AHS Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                                <div>
                                    <x-input-label for="code" :value="__('AHS Code')" />
                                    <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required autofocus />
                                    <p class="text-xs text-gray-500 mt-1">Unique code, e.g., AHS.CONC.K225</p>
                                </div>
                                <div>
                                    <x-input-label for="name" :value="__('AHS Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                                    <p class="text-xs text-gray-500 mt-1">e.g., Analisa Beton K-225</p>
                                </div>
                                <div>
                                    <x-input-label for="unit" :value="__('Unit')" />
                                    <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit')" required />
                                    <p class="text-xs text-gray-500 mt-1">Unit for the analysis, e.g., m³, m², bh</p>
                                </div>
                                <div>
                                    <x-input-label for="overhead_profit_percentage" :value="__('Overhead/Profit (%)')" />
                                    <x-text-input x-model.number="overheadProfitPercentage" id="overhead_profit_percentage" class="block mt-1 w-full" type="number" step="0.01" name="overhead_profit_percentage" :value="old('overhead_profit_percentage', 0)" required min="0" max="100"/>
                                    <p class="text-xs text-gray-500 mt-1">e.g., 15 for 15%</p>
                                </div>
                            </div>
                            <div>
                                <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            </div>

                            <hr class="my-6">
                            <h3 class="text-lg font-semibold mb-2">Materials</h3>
                            <div class="space-y-3">
                                <template x-for="(material, index) in materials" :key="`mat-${index}`">
                                    <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border" x-init="initializeTomSelect($el.querySelector('.tom-select-materials'))">
                                        <div class="flex-1">
                                            <label :for="`mat_id_${index}`" class="text-xs font-medium text-gray-700">Material</label>
                                            <select x-model="material.inventory_item_id" @change="updateMaterialCost(index, $event.target.value)" :id="`mat_id_${index}`" :name="`materials[${index}][inventory_item_id]`" class="tom-select-materials block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required>
                                                <option value="">Select material...</option>
                                                @foreach ($inventoryItems as $item)
                                                    {{-- Storing cost in data attribute --}}
                                                    <option value="{{ $item->id }}" data-cost="{{ $item->latest_cost ?? 0 }}">{{ $item->item_code }} - {{ $item->item_name }} ({{ $item->uom }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-28">
                                            <label :for="`mat_coeff_${index}`" class="text-xs font-medium text-gray-700">Coefficient</label>
                                            <input x-model.number="material.coefficient" :id="`mat_coeff_${index}`" type="number" step="0.0001" :name="`materials[${index}][coefficient]`" class="block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required>
                                        </div>
                                        <div class="w-32">
                                            <label :for="`mat_cost_${index}`" class="text-xs font-medium text-gray-700">Unit Cost (Rp)</label>
                                            <input x-model.number="material.unit_cost" :id="`mat_cost_${index}`" type="number" step="0.01" :name="`materials[${index}][unit_cost]`" class="block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required>
                                        </div>
                                        <div class="w-32 pt-5 text-sm font-medium text-right" x-text="formatCurrency(material.coefficient * material.unit_cost)"></div>
                                        <div class="pt-5">
                                            <button type="button" @click="removeMaterial(index)" class="text-red-500 hover:text-red-700">✖</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addMaterial()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Add Material</button>

                            <hr class="my-6">
                            <h3 class="text-lg font-semibold mb-2">Labor</h3>
                             <div class="space-y-3">
                                <template x-for="(labor, index) in labors" :key="`lab-${index}`">
                                     <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded border" x-init="initializeTomSelect($el.querySelector('.tom-select-labors'))">
                                        <div class="flex-1">
                                            <label :for="`lab_id_${index}`" class="text-xs font-medium text-gray-700">Labor Type</label>
                                             <select x-model="labor.labor_rate_id" @change="updateLaborRate(index, $event.target.value)" :id="`lab_id_${index}`" :name="`labors[${index}][labor_rate_id]`" class="tom-select-labors block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required>
                                                <option value="">Select labor...</option>
                                                @foreach ($laborRates as $rate)
                                                    {{-- Storing rate in data attribute --}}
                                                    <option value="{{ $rate->id }}" data-rate="{{ $rate->rate }}">{{ $rate->labor_type }} ({{ $rate->unit }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-28">
                                            <label :for="`lab_coeff_${index}`" class="text-xs font-medium text-gray-700">Coefficient</label>
                                            <input x-model.number="labor.coefficient" :id="`lab_coeff_${index}`" type="number" step="0.0001" :name="`labors[${index}][coefficient]`" class="block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required>
                                        </div>
                                        <div class="w-32">
                                            <label :for="`lab_rate_${index}`" class="text-xs font-medium text-gray-700">Rate (Rp)</label>
                                            <input x-model.number="labor.rate" :id="`lab_rate_${index}`" type="number" step="0.01" :name="`labors[${index}][rate]`" class="block mt-1 w-full border-gray-300 text-sm rounded-md shadow-sm" required>
                                        </div>
                                        <div class="w-32 pt-5 text-sm font-medium text-right" x-text="formatCurrency(labor.coefficient * labor.rate)"></div>
                                        <div class="pt-5">
                                            <button type="button" @click="removeLabor(index)" class="text-red-500 hover:text-red-700">✖</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addLabor()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Add Labor</button>

                            <hr class="my-6">
                            <div class="flex justify-end">
                                <div class="w-64 text-right">
                                    <p class="text-sm text-gray-600">Total Material Cost: <span x-text="formatCurrency(totalMaterialCost)"></span></p>
                                    <p class="text-sm text-gray-600">Total Labor Cost: <span x-text="formatCurrency(totalLaborCost)"></span></p>
                                    <p class="text-sm text-gray-600 border-b pb-1 mb-1">Base Cost (Mat+Labor): <span class="font-semibold" x-text="formatCurrency(baseTotalCost)"></span></p>
                                    <p class="text-sm text-gray-600">Overhead & Profit (<span x-text="overheadProfitPercentage"></span>%): <span x-text="formatCurrency(overheadProfitAmount)"></span></p>
                                    <p class="text-lg font-bold mt-1 pt-1">Final Unit Cost: <span x-text="formatCurrency(grandTotal)"></span></p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6 border-t pt-4">
                                <a href="{{ route('ahs-library.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md"> {{ __('Cancel') }} </a>
                                <x-primary-button class="ml-4"> {{ __('Save AHS') }} </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Store master data costs/rates for easy JS lookup
            const inventoryItemData = @json($inventoryItems->mapWithKeys(fn($item) => [$item->id => ['cost' => $item->latest_cost ?? 0]]));
            const laborRatesData = @json($laborRates->mapWithKeys(fn($rate) => [$rate->id => ['rate' => $rate->rate]]));

            function ahsForm() {
                return {
                    materials: [],
                    labors: [],
                    overheadProfitPercentage: {{ old('overhead_profit_percentage', 0) }}, // Initialize from old input or default

                    addMaterial() {
                        this.materials.push({ inventory_item_id: '', coefficient: 0, unit_cost: 0 });
                        this.$nextTick(() => { this.initializeSelects('.tom-select-materials'); });
                    },
                    removeMaterial(index) {
                        this.destroySelect(this.$el.querySelectorAll('.tom-select-materials')[index]);
                        this.materials.splice(index, 1);
                    },
                    updateMaterialCost(index, itemId) {
                         const selectedOption = event.target.options[event.target.selectedIndex];
                         this.materials[index].unit_cost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
                    },

                    addLabor() {
                        this.labors.push({ labor_rate_id: '', coefficient: 0, rate: 0 });
                         this.$nextTick(() => { this.initializeSelects('.tom-select-labors'); });
                    },
                    removeLabor(index) {
                         this.destroySelect(this.$el.querySelectorAll('.tom-select-labors')[index]);
                        this.labors.splice(index, 1);
                    },
                    updateLaborRate(index, rateId) {
                        const selectedOption = event.target.options[event.target.selectedIndex];
                        this.labors[index].rate = parseFloat(selectedOption.getAttribute('data-rate')) || 0;
                    },

                    // Calculation Properties
                    get totalMaterialCost() {
                        return this.materials.reduce((sum, item) => sum + ((item.coefficient || 0) * (item.unit_cost || 0)), 0);
                    },
                    get totalLaborCost() {
                        return this.labors.reduce((sum, item) => sum + ((item.coefficient || 0) * (item.rate || 0)), 0);
                    },
                    get baseTotalCost() {
                        return this.totalMaterialCost + this.totalLaborCost; // Add equipment later
                    },
                     get overheadProfitAmount() {
                        return this.baseTotalCost * (this.overheadProfitPercentage / 100);
                    },
                    get grandTotal() {
                        return this.baseTotalCost + this.overheadProfitAmount;
                    },

                    // Helper Functions
                    formatCurrency(value) {
                         if (isNaN(value)) return 'Rp 0';
                        return value.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
                    },
                    initializeSelects(selector) {
                         // Initialize TomSelect on the *last* matching element that isn't initialized
                        let selects = this.$el.querySelectorAll(selector + ':not(.ts-wrapper)');
                        if (selects.length > 0) {
                            let lastSelect = selects[selects.length - 1];
                            if (lastSelect && !lastSelect.tomselect) {
                                new TomSelect(lastSelect, { create: false, sortField: { field: "text", direction: "asc" } });
                            }
                        }
                    },
                    destroySelect(element) {
                        if (element && element.tomselect) {
                            element.tomselect.destroy();
                        }
                    },
                    init() {
                         // Initialize any selects present on initial load
                         this.$nextTick(() => {
                             this.$el.querySelectorAll('.tom-select-materials, .tom-select-labors').forEach(el => {
                                 if (!el.tomselect) {
                                     new TomSelect(el, { create: false, sortField: { field: "text", direction: "asc" } });
                                 }
                             });
                         });
                         // Add one empty row if none exist from old input
                         if (this.materials.length === 0 && {{ count(old('materials', [])) }} === 0) this.addMaterial();
                         if (this.labors.length === 0 && {{ count(old('labors', [])) }} === 0) this.addLabor();
                    }
                }
            }
        </script>
    </x-app-layout>
