<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Log Progress for: {{ $project->quotation->project_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('progress.store', $project) }}">
                        @csrf
                        
                        {{-- Main Progress Details --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label for="quotation_item_id" class="block font-medium text-sm text-gray-700">Task (WBS Item)</label>
                                <select id="quotation_item_id" name="quotation_item_id" class="task-select block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">-- Select a Task --</option>
                                    @foreach ($tasks as $task)
                                        <option value="{{ $task->id }}" {{ old('quotation_item_id') == $task->id ? 'selected' : '' }}>
                                            {{ $task->description }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('quotation_item_id')" class="mt-2" />
                            </div>
                            
                            <div>
                                <label for="date" class="block font-medium text-sm text-gray-700">Date</label>
                                <input type="date" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>

                            {{-- === THIS IS THE NEW FIELD === --}}
                            <div>
                                <label for="percent_complete" class="block font-medium text-sm text-gray-700">New Total Progress (%)</label>
                                <input type="number" step="0.01" min="0" max="100" id="percent_complete" name="percent_complete" value="{{ old('percent_complete') }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <x-input-error :messages="$errors->get('percent_complete')" class="mt-2" />
                            </div>
                            {{-- === END OF NEW FIELD === --}}
                        </div>

                        <div class="mb-6">
                            <label for="notes" class="block font-medium text-sm text-gray-700">Notes (Optional)</label>
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        {{-- Alpine Component for Dynamic Rows --}}
                        <div x-data="costLogger()">
                            
                            {{-- 1. Materials Used --}}
                            <h3 class="text-lg font-semibold border-b pb-2 mb-4">Materials Used</h3>
                            <div class="space-y-4">
                                <template x-for="(material, index) in materials" :key="index">
                                    <div class="grid grid-cols-12 gap-4 items-center">
                                        <div class="col-span-7">
                                            <label class="sr-only">Material Item</label>
                                            <select :name="`materials[${index}][inventory_item_id]`" class="item-select block w-full border-gray-300 rounded-md shadow-sm" x-init="initializeSelects($el)">
                                                <option value="">-- Select Material --</option>
                                                @foreach ($inventoryItems as $item)
                                                    <option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->uom }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-3">
                                            <label class="sr-only">Quantity</label>
                                            <input type="number" step="0.01" :name="`materials[${index}][quantity_used]`" placeholder="Quantity Used" class="block w-full border-gray-300 rounded-md shadow-sm">
                                        </div>
                                        <div class="col-span-2">
                                            <button type="button" @click="removeMaterial(index)" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addMaterial" class="mt-4 text-sm text-blue-600 hover:text-blue-800">+ Add Material</button>
                            <x-input-error :messages="$errors->get('materials')" class="mt-2" />

                            {{-- 2. Labor Used --}}
                            <h3 class="text-lg font-semibold border-b pb-2 mb-4 mt-8">Labor Used</h3>
                            <div class="space-y-4">
                                <template x-for="(labor, index) in labors" :key="index">
                                    <div class="grid grid-cols-12 gap-4 items-center">
                                        <div class="col-span-7">
                                            <label class="sr-only">Labor Type</label>
                                            <select :name="`labors[${index}][labor_rate_id]`" class="item-select block w-full border-gray-300 rounded-md shadow-sm" x-init="initializeSelects($el)">
                                                <option value="">-- Select Labor --</option>
                                                @foreach ($laborRates as $rate)
                                                    <option value="{{ $rate->id }}">{{ $rate->labor_type }} ({{ $rate->unit }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-3">
                                            <label class="sr-only">Quantity</label>
                                            <input type="number" step="0.01" :name="`labors[${index}][quantity_used]`" placeholder="Quantity Used" class="block w-full border-gray-300 rounded-md shadow-sm">
                                        </div>
                                        <div class="col-span-2">
                                            <button type="button" @click="removeLabor(index)" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" @click="addLabor" class="mt-4 text-sm text-blue-600 hover:text-blue-800">+ Add Labor</button>
                            <x-input-error :messages="$errors->get('labors')" class="mt-2" />

                        </div>

                        {{-- Form Actions --}}
                        <div class="flex items-center justify-end mt-8 border-t pt-6">
                            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                {{ __('Log Progress') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('head-scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('costLogger', () => ({
                materials: [],
                labors: [],
                
                init() {
                    @if(old('materials'))
                        this.materials = @json(old('materials')).map(m => ({...m}));
                    @else
                        this.addMaterial();
                    @endif

                    @if(old('labors'))
                        this.labors = @json(old('labors')).map(l => ({...l}));
                    @else
                        this.addLabor();
                    @endif

                    this.initializeSelects(document.querySelector('.task-select'));
                },

                addMaterial() {
                    this.materials.push({});
                },
                removeMaterial(index) {
                    this.materials.splice(index, 1);
                },

                addLabor() {
                    this.labors.push({});
                },
                removeLabor(index) {
                    this.labors.splice(index, 1);
                },

                initializeSelects(element) {
                    if (typeof TomSelect !== 'undefined' && element && !element.tomselect) {
                        new TomSelect(element, { create: false, sortField: { field: "text", direction: "asc" } });
                    }
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>