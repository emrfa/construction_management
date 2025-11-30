<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Log Project Progress') }}: {{ $project->quotation->project_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div x-data="progressForm({
                    materials: {{ json_encode(old('materials', [])) }},
                    labors: {{ json_encode(old('labors', [])) }},
                    equipments: {{ json_encode(old('equipments', [])) }},
                    stockLocationExists: {{ $stockLocation ? 'true' : 'false' }}
                })">
                    <form method="POST" action="{{ route('progress.store', $project) }}">
                        @csrf

                        <input type="hidden" name="materials_json" x-model="JSON.stringify(materials)">
                        <input type="hidden" name="labors_json" x-model="JSON.stringify(labors)">
                        <input type="hidden" name="equipments_json" x-model="JSON.stringify(equipments)">

                        <div class="p-6 text-gray-900 space-y-6">

                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Progress Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="quotation_item_id" :value="__('Project Task (WBS)')" />
                                    <select id="quotation_item_id" name="quotation_item_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" x-init="initializeTomSelect($el)" required>
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
                            
                            {{-- UPDATED: Show location, or an error --}}
                            @if($stockLocation)
                                <div class="p-3 bg-blue-50 border border-blue-200 rounded-md">
                                    <p class="font-medium text-blue-800">
                                        Materials will be consumed from: 
                                        <strong>{{ $stockLocation->name }} ({{ $stockLocation->code }})</strong>
                                    </p>
                                </div>
                            @else
                                <div class="p-3 bg-red-100 border border-red-300 rounded-md">
                                    <p class="font-medium text-red-800">
                                        Error: This project has no stock location assigned. Material usage cannot be recorded.
                                    </p>
                                </div>
                            @endif
                            
                            {{-- Hide material section if no location exists --}}
                            <div x-show="stockLocationExists">
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
                            </div>

                            {{-- 3. LABOR USED (Unchanged) --}}
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

                            {{-- 4. EQUIPMENT USED (Unchanged) --}}
                            <hr />
                            <h3 class="text-lg font-medium text-gray-900">Equipment Used</h3>
                            <div class="space-y-2">
                                <template x-for="(equipment, index) in equipments" :key="index">
                                    <div class="flex items-center space-x-2">
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
                                        <x-text-input type="number" step="0.01" x-model.number="equipment.quantity" class="block w-24 text-sm" placeholder="Qty" />
                                        <select x-model="equipment.unit" class="block w-28 border-gray-300 text-sm rounded-md shadow-sm">
                                            <option value="Hour">Hour</option>
                                            <option value="Day">Day</option>
                                            <option value="Week">Week</option>
                                        </select>
                                        <button type="button" @click="removeEquipment(index)" class="text-red-500 hover:text-red-700 p-1">✖</button>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addEquipment()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">+ Add Equipment</button>

                        </div>

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
    // Prevent scroll from changing number input values
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[type=number]').forEach(input => {
            input.addEventListener('wheel', (e) => {
                e.preventDefault();
            });
        });
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('progressForm', (initialData) => ({
            materials: initialData.materials,
            labors: initialData.labors,
            equipments: initialData.equipments,
            stockLocationExists: initialData.stockLocationExists, // <-- Get this from PHP
            tomSelects: { mat: [], lab: [], eq: [], other: [] },

            init() {
                this.$nextTick(() => {
                    this.initializeTomSelect(document.getElementById('quotation_item_id'));
                    
                    this.materials.forEach((_, i) => this.initTomSelectInLoop('mat', i));
                    this.labors.forEach((_, i) => this.initTomSelectInLoop('lab', i));
                    this.equipments.forEach((_, i) => this.initTomSelectInLoop('eq', i));
                });
            },

            addMaterial() {
                this.materials.push({ id: '', quantity: '' });
                this.$nextTick(() => this.initTomSelectInLoop('mat', this.materials.length - 1));
            },
            removeMaterial(index) {
                this.destroyTomSelectInLoop('mat', index);
                this.materials.splice(index, 1);
            },

            addLabor() {
                this.labors.push({ id: '', quantity: '' });
                this.$nextTick(() => this.initTomSelectInLoop('lab', this.labors.length - 1));
            },
            removeLabor(index) {
                this.destroyTomSelectInLoop('lab', index);
                this.labors.splice(index, 1);
            },

            addEquipment() {
                this.equipments.push({ id: '', quantity: '', unit: 'Day' });
                this.$nextTick(() => this.initTomSelectInLoop('eq', this.equipments.length - 1));
            },
            removeEquipment(index) {
                this.destroyTomSelectInLoop('eq', index);
                this.equipments.splice(index, 1);
            },

            initTomSelectInLoop(type, index) {
                if (typeof TomSelect === 'undefined') return;
                const el = this.$el.querySelectorAll(`.tom-select-${type}`)[index];
                if (el && !el.tomselect) {
                    this.tomSelects[type][index] = new TomSelect(el, {
                        create: false,
                        sortField: { field: "text", direction: "asc" }
                    });
                }
            },
            destroyTomSelectInLoop(type, index) {
                if (this.tomSelects[type][index]) {
                    this.tomSelects[type][index].destroy();
                    this.tomSelects[type].splice(index, 1);
                }
            },
            
            initializeTomSelect(element) {
                if (typeof TomSelect === 'undefined' || !element || element.tomselect) return;
                let instance = new TomSelect(element, {
                    create: false,
                    sortField: { field: "text", direction: "asc" }
                });
                this.tomSelects.other.push(instance);
            }
        }));
    });
    </script>
    @endpush
</x-app-layout>