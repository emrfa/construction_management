<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Inventory Item') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('inventory-items.update', $inventoryItem) }}">
                        @csrf
                        @method('PATCH')
                        
                        <div>
                            <x-input-label for="item_code" :value="__('Item Code')" />
                            <x-text-input id="item_code" class="block mt-1 w-full bg-gray-100" type="text" name="item_code" :value="$inventoryItem->item_code" disabled readonly />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="category_id" :value="__('Item Category')" />
                            {{-- This ID is for Tom Select --}}
                            <select id="select-category" name="category_id" required placeholder="-- Select a category --">
                                <option value="">-- Select a category --</option>
                                @foreach($categories as $category)
                                    {{-- THIS LINE IS CHANGED --}}
                                    <option value="{{ $category->id }}" {{ old('category_id', $inventoryItem->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">Changing category will not change the existing item code.</p>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="item_name" :value="__('Item Name')" />
                            <x-text-input id="item_name" class="block mt-1 w-full" type="text" name="item_name" :value="old('item_name', $inventoryItem->item_name)" required autofocus />
                            <x-input-error :messages="$errors->get('item_name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="uom" :value="__('Unit of Measure (UOM)')" />
                            <x-text-input id="uom" class="block mt-1 w-full" type="text" name="uom" :value="old('uom', $inventoryItem->uom)" required />
                            <x-input-error :messages="$errors->get('uom')" class="mt-2" />
                        </div>
                        
                        <div class="mt-4">
                            <x-input-label for="base_purchase_price" :value="__('Base Purchase Price (Optional)')" />
                            <x-text-input id="base_purchase_price" class="block mt-1 w-full" type="number" step="0.01" name="base_purchase_price" :value="old('base_purchase_price', $inventoryItem->base_purchase_price)" />
                            <x-input-error :messages="$errors->get('base_purchase_price')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="reorder_level" :value="__('Reorder Level')" />
                            <x-text-input id="reorder_level" class="block mt-1 w-full" type="number" step="1" name="reorder_level" :value="old('reorder_level', $inventoryItem->reorder_level)" />
                            <x-input-error :messages="$errors->get('reorder_level')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('inventory-items.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Item') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect('#select-category', {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    </script>
    @endpush
</x-app-layout>