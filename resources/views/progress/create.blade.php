<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; {{ $project->project_code }}
            </a>
            <span class="text-gray-500">/</span>
            <span>Add Progress Update</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div x-data="progressForm()">

                        <form method="POST" action="{{ route('progress.store', $project) }}">
                            @csrf

                            <div>
                                <x-input-label for="quotation_item_id" :value="__('Task')" />
                                <select id="quotation_item_id" name="quotation_item_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select a task to update...</option>
                                    @foreach ($tasks as $task)
                                        <option value="{{ $task->id }}">
                                            {{ $task->description }} (Current: {{ $task->latest_progress }}%)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="date" :value="__('Date of Work')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', date('Y-m-d'))" required />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="percent_complete" :value="__('New Percent Complete (Total)')" />
                                <x-text-input id="percent_complete" class="block mt-1 w-full" type="number" name="percent_complete" :value="old('percent_complete')" min="0" max="100" required />
                                <p class="text-sm text-gray-600 mt-1">Enter the new *total* percentage for this task (e.g., if it was 20% and you did 10% more, enter 30).</p>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                <textarea id="notes" name="notes" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            </div>

                            <hr class="my-6">

                            <h3 class="text-lg font-semibold mb-2">Materials Used (Optional)</h3>
                            <div class="space-y-4">
                                <template x-for="(material, index) in materials" :key="index">
                                    <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded" x-init="initializeTomSelect($el.querySelector('.tom-select-materials'))">
                                        <div class="flex-1">
                                            <label :for="`material_id_${index}`" class="block font-medium text-sm text-gray-700">{{ __('Material') }}</label>
                                            <select x-model="material.inventory_item_id" :id="`material_id_${index}`" :name="`materials[${index}][inventory_item_id]`" class="tom-select-materials block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                <option value="">Select material...</option>
                                                @foreach ($inventoryItems as $invItem)
                                                    <option value="{{ $invItem->id }}">{{ $invItem->item_code }} - {{ $invItem->item_name }} ({{ $invItem->uom }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-24">
                                            <label :for="`material_qty_${index}`" class="block font-medium text-sm text-gray-700">{{ __('Qty Used') }}</label>
                                            <input x-model.number="material.quantity_used" :id="`material_qty_${index}`" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="number" :name="`materials[${index}][quantity_used]`" min="0" step="0.01" />
                                        </div>
                                        <div class="pt-5">
                                            <button type="button" @click="removeMaterial(index)" class="text-red-500 hover:text-red-700 font-bold p-2"> X </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="mt-4">
                                <button type="button" @click="addNewMaterial()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded text-sm">
                                    + Add Material Used
                                </button>
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('projects.show', $project) }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                    {{ __('Cancel') }}
                                </a>

                                <x-primary-button class="ml-4">
                                    {{ __('Save Progress') }}
                                </x-primary-button>
                            </div>
                        </form>

                    </div> </div>
            </div>
        </div>
    </div>

    <script>
        function progressForm() {
            return {
                materials: [ { inventory_item_id: '', quantity_used: 1 } ],
                addNewMaterial() {
                    this.materials.push({ inventory_item_id: '', quantity_used: 1 });
                    // Use $nextTick to ensure the DOM is updated before initializing Tom Select
                    this.$nextTick(() => {
                        let selects = this.$el.querySelectorAll('.tom-select-materials:not(.ts-wrapper)'); // Target only non-initialized selects
                        if (selects.length > 0) {
                             // Initialize the *last* one added
                            this.initializeTomSelect(selects[selects.length - 1]);
                        }
                    });
                },
                removeMaterial(index) {
                     // Find the select element before removing the data
                    let row = this.$el.querySelectorAll('.flex.items-center.space-x-2.p-2.bg-gray-50.rounded')[index];
                    let select = row ? row.querySelector('.tom-select-materials') : null;
                    // Destroy Tom Select instance if it exists
                    if (select && select.tomselect) {
                        select.tomselect.destroy();
                    }
                    this.materials.splice(index, 1);
                },
                 // Function to initialize TomSelect on a given element
                initializeTomSelect(element) {
                    if (element && !element.tomselect) { // Check if not already initialized
                       new TomSelect(element, {
                            create: false,
                            sortField: { field: "text", direction: "asc" }
                        });
                    }
                },
                // Initialize TomSelect for the main task dropdown on page load
                init() {
                     new TomSelect('#quotation_item_id',{
                        create: false,
                        sortField: { field: "text", direction: "asc" }
                    });
                    // Initialize TomSelect for any initial material rows (if needed)
                    // this.$nextTick(() => {
                    //     this.$el.querySelectorAll('.tom-select-materials').forEach(el => this.initializeTomSelect(el));
                    // });
                }
            }
        }

        // Initialize the component when the DOM is ready
        // document.addEventListener('alpine:init', () => {
        //     Alpine.data('progressForm', progressForm);
        // });
        // Removed DOMContentLoaded listener as Alpine handles init now
    </script>
</x-app-layout>