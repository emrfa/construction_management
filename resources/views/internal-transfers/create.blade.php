<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Internal Transfers', 'url' => route('internal-transfers.index')],
            ['label' => 'New Transfer', 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Internal Transfer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('internal-transfers.store') }}" id="transfer-form">
                        @csrf
                        
                        @if(request('material_request_id'))
                            <input type="hidden" name="material_request_id" value="{{ request('material_request_id') }}">
                            <div class="mb-4 p-4 bg-blue-50 text-blue-700 rounded-lg border border-blue-200">
                                Creating transfer for Material Request #{{ request('material_request_id') }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <x-input-label for="source_location_id" :value="__('Source Location')" />
                                <select id="source_location_id" name="source_location_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Source</option>
                                    @foreach($sourceLocations as $location)
                                        <option value="{{ $location->id }}" {{ old('source_location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }} ({{ $location->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('source_location_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="destination_location_id" :value="__('Destination Location')" />
                                <select id="destination_location_id" name="destination_location_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select Destination</option>
                                    @foreach($destinationLocations as $location)
                                        <option value="{{ $location->id }}" {{ old('destination_location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }} ({{ $location->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('destination_location_id')" class="mt-2" />
                            </div>
                            
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Notes')" />
                                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Items</h3>
                            
                            <div id="items-container" class="space-y-4">
                                {{-- Items will be added here via JS --}}
                            </div>

                            <button type="button" onclick="addItemRow()" class="mt-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">
                                + Add Item
                            </button>
                        </div>

                        <input type="hidden" name="items_json" id="items_json">

                        <div class="flex items-center justify-end mt-8 gap-4">
                            <a href="{{ route('internal-transfers.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <button type="submit" onclick="prepareSubmit(event)" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 shadow-sm">
                                Create Transfer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let items = [];
        const inventoryItems = @json($inventoryItems);
        const prefilledItems = @json($prefilledItems ?? []);
        const prefilledDestinationId = @json($prefilledDestinationId ?? null);

        // Initialize static selects
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect('#source_location_id', {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: 'Select Source Location'
            });
            
            const destSelect = new TomSelect('#destination_location_id', {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: 'Select Destination Location'
            });

            if (prefilledDestinationId) {
                destSelect.setValue(prefilledDestinationId);
            }

            // Load prefilled items or default empty row
            if (prefilledItems.length > 0) {
                // Clear existing (if any, though usually empty on load)
                document.getElementById('items-container').innerHTML = '';
                items = []; // Reset array
                
                prefilledItems.forEach(item => {
                    addItemRow(item);
                });
            } else {
                addItemRow();
            }
        });

        function addItemRow(itemData = null) {
            const container = document.getElementById('items-container');
            const index = container.children.length; // Use length as unique index for now.
            
            const div = document.createElement('div');
            div.className = 'grid grid-cols-12 gap-4 items-start p-4 bg-gray-50 rounded-lg border border-gray-200'; 
            
            let options = '<option value="">Select Item</option>';
            inventoryItems.forEach(item => {
                const selected = itemData && itemData.inventory_item_id == item.id ? 'selected' : '';
                options += `<option value="${item.id}" ${selected}>${item.item_name} (${item.item_code})</option>`;
            });

            div.innerHTML = `
                <div class="col-span-8">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Item</label>
                    <select class="item-select block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" onchange="updateItem(${index}, 'inventory_item_id', this.value)">
                        ${options}
                    </select>
                </div>
                <div class="col-span-3">
                    <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" step="0.01" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm h-[42px]" 
                           value="${itemData ? itemData.quantity_requested : ''}" 
                           onchange="updateItem(${index}, 'quantity_requested', this.value)" placeholder="0.00">
                </div>
                <div class="col-span-1 pt-6">
                    <button type="button" onclick="removeItem(this, ${index})" class="text-red-600 hover:text-red-800 p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
            `;
            
            container.appendChild(div);
            items[index] = itemData || { inventory_item_id: null, quantity_requested: 0 };

            // Initialize TomSelect on the new select
            const newSelect = div.querySelector('.item-select');
            new TomSelect(newSelect, {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: 'Search Item...',
                onChange: function(value) {
                    updateItem(index, 'inventory_item_id', value);
                }
            });
        }

        function updateItem(index, field, value) {
            if (!items[index]) items[index] = {};
            items[index][field] = value;
        }

        function removeItem(btn, index) {
            btn.closest('.grid').remove();
            items[index] = null; // Mark as removed
        }

        function prepareSubmit(e) {
            e.preventDefault();
            const validItems = items.filter(i => i !== null && i.inventory_item_id && i.quantity_requested > 0);
            
            if (validItems.length === 0) {
                alert('Please add at least one item with a valid quantity.');
                return;
            }

            document.getElementById('items_json').value = JSON.stringify(validItems);
            document.getElementById('transfer-form').submit();
        }

        // Initialize is handled in DOMContentLoaded now
    </script>
</x-app-layout>
