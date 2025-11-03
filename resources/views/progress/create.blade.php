<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Log Project Progress') }}: {{ $project->quotation->project_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                
                {{-- This Alpine.js component will manage all our dynamic rows --}}
                <div x-data="progressForm({
                    materials: {{ json_encode(old('materials', [])) }},
                    labors: {{ json_encode(old('labors', [])) }},
                    equipments: {{ json_encode(old('equipments', [])) }}
                })">
                    <form method="POST" action="{{ route('progress.store', $project) }}">
                        @csrf
                        
                        {{-- Hidden inputs to store the JSON data --}}
                        <input type="hidden" name="materials_json" x-model="JSON.stringify(materials)">
                        <input type="hidden" name="labors_json" x-model="JSON.stringify(labors)">
                        <input type="hidden" name="equipments_json" x-model="JSON.stringify(equipments)">

                        <div class="p-6 text-gray-900 space-y-6">

                            {{-- 1. MAIN DETAILS --}}
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Progress Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="quotation_item_id" :value="__('Project Task (WBS)')" />
                                    <select id="quotation_item_id" name="quotation_item_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                        <option value="">-- Select a task --</option>
                                        @foreach($tasks as $task)
                                            <option value="{{ $task->id }}" {{ old('quotation_item_id') == $task->id ? 'selected' : '' }}>
                                                {{ $task->description }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('quotation_item_id')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="date" :value="__('Date')" />
                                    <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', date('Y-m-d'))" required />
                                    <x-input-error :messages="$errors->get('date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="percent_complete" :value="__('New Percent Complete (%)')" />
                                    <x-text-input id="percent_complete" class="block mt-1 w-full" type="number" step="0.01" name="percent_complete" :value="old('percent_complete')" required />
                                    <x-input-error :messages="$errors->get('percent_complete')" class="mt-2" />
                                </div>
                                <div class="md:col-span-3">
                                    <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                    <textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>

                            {{-- 2. MATERIALS USED --}}
                            <hr />
                            <h3 class="text-lg font-medium text-gray-900">Materials Used</h3>
                            <div class="space-y-2">
                                <template x-for="(material, index) in materials" :key="index">
                                    <div class="flex items-center space-x-2">
                                        <select x-model="material.id" class="tom-select-mat block w-full border-gray-300 text-sm rounded-md shadow-sm" placeholder="Select material...">
                                            <option value="">Select material...</option>
                                            @foreach($inventoryItems as $item)
                                                <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->uom }})</option>
                                            @endforeach
                                        </select>
                                        <x-text-input type="number" step="0.01" x-model.number="material.quantity" class="block w-32 text-sm" placeholder="Qty" />
                                        <button type="button" @click="removeMaterial(index)" class="text-red-500 hover:text-red-700 p-1">✖</button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addMaterial()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Add Material</button>

                            {{-- 3. LABOR USED --}}
                            <hr />
                            <h3 class="text-lg font-medium text-gray-900">Labor Used</h3>
                            <div class="space-y-2">
                                <template x-for="(labor, index) in labors" :key="index">
                                    <div class="flex items-center space-x-2">
                                        <select x-model="labor.id" class="tom-select-lab block w-full border-gray-300 text-sm rounded-md shadow-sm" placeholder="Select labor...">
                                            <option value="">Select labor...</option>
                                            @foreach($laborRates as $rate)
                                                <option value="{{ $rate->id }}">{{ $rate->labor_type }} ({{ $rate->unit }})</option>
                                            @endforeach
                                        </select>
                                        <x-text-input type="number" step="0.01" x-model.number="labor.quantity" class="block w-32 text-sm" placeholder="Qty" />
                                        <button type="button" @click="removeLabor(index)" class="text-red-500 hover:text-red-700 p-1">✖</button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addLabor()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Add Labor</button>

                            {{-- 4. EQUIPMENT USED (NEW) --}}
                            <hr />
                            <h3 class="text-lg font-medium text-gray-900">Equipment Used</h3>
                            <div class="space-y-2">
                                <template x-for="(equipment, index) in equipments" :key="index">
                                    <div class="flex items-center space-x-2">
                                        {{-- Equipment Dropdown --}}
                                        <select x-model="equipment.id" class="tom-select-eq block w-full border-gray-300 text-sm rounded-md shadow-sm" placeholder="Select equipment...">
                                            <option value="">Select equipment...</option>
                                            @foreach($equipments as $eq)
                                                <option value="{{ $eq->id }}">
                                                    {{ $eq->name }} 
                                                    (@if($eq->status == 'rented')
                                                        Rp {{ number_format($eq->rental_rate, 0) }}/{{ $eq->rental_rate_unit }}
                                                    @else
                                                        Rp {{ number_format($eq->base_rental_rate, 0) }}/{{ $eq->base_rental_rate_unit }}
                                                    @endif)
                                                </option>
                                            @endforeach
                                        </select>
                                        {{-- Quantity Input --}}
                                        <x-text-input type="number" step="0.01" x-model.number="equipment.quantity" class="block w-24 text-sm" placeholder="Qty" />
                                        {{-- Unit Dropdown --}}
                                        <select x-model="equipment.unit" class="block w-28 border-gray-300 text-sm rounded-md shadow-sm">
                                            <option value="Hour">Hour</option>
                                            <option value="Day">Day</option>
                                            <option value="Week">Week</option>
                                        </select>
                                        {{-- Remove Button --}}
                                        <button type="button" @click="removeEquipment(index)" class="text-red-500 hover:text-red-700 p-1">✖</button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addEquipment()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Add Equipment</button>

                        </div>

                        {{-- FORM ACTIONS --}}
                        <div class="flex items-center justify-end p-6 bg-gray-50 border-t">
                            <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Save Progress') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('progressForm', (initialData) => ({
            materials: initialData.materials,
            labors: initialData.labors,
            equipments: initialData.equipments,
            tomSelects: { mat: [], lab: [], eq: [] },

            init() {
                // Initialize existing TomSelects on page load (e.g., from validation error)
                this.$nextTick(() => {
                    this.materials.forEach((_, i) => this.initTomSelect('mat', i));
                    this.labors.forEach((_, i) => this.initTomSelect('lab', i));
                    this.equipments.forEach((_, i) => this.initTomSelect('eq', i));
                });
            },

            // --- Material Methods ---
            addMaterial() {
                this.materials.push({ id: '', quantity: '' });
                this.$nextTick(() => this.initTomSelect('mat', this.materials.length - 1));
            },
            removeMaterial(index) {
                this.destroyTomSelect('mat', index);
                this.materials.splice(index, 1);
            },

            // --- Labor Methods ---
            addLabor() {
                this.labors.push({ id: '', quantity: '' });
                this.$nextTick(() => this.initTomSelect('lab', this.labors.length - 1));
            },
            removeLabor(index) {
                this.destroyTomSelect('lab', index);
                this.labors.splice(index, 1);
            },

            // --- Equipment Methods (NEW) ---
            addEquipment() {
                this.equipments.push({ id: '', quantity: '', unit: 'Day' }); // Default to 'Day'
                this.$nextTick(() => this.initTomSelect('eq', this.equipments.length - 1));
            },
            removeEquipment(index) {
                this.destroyTomSelect('eq', index);
                this.equipments.splice(index, 1);
            },

            // --- TomSelect Helpers ---
            initTomSelect(type, index) {
                if (typeof TomSelect === 'undefined') return;
                const el = this.$el.querySelectorAll(`.tom-select-${type}`)[index];
                if (el && !el.tomselect) {
                    this.tomSelects[type][index] = new TomSelect(el, {
                        create: false,
                        sortField: { field: "text", direction: "asc" }
                    });
                }
            },
            destroyTomSelect(type, index) {
                if (this.tomSelects[type][index]) {
                    this.tomSelects[type][index].destroy();
                    this.tomSelects[type].splice(index, 1);
                }
            }
        }));
    });
    </script>
    @endpush
</x-app-layout>