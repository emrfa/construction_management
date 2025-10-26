<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Item to Master') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('inventory-items.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="item_code" :value="__('Item Code (SKU)')" />
                            <x-text-input id="item_code" class="block mt-1 w-full" type="text" name="item_code" :value="old('item_code')" required autofocus />
                            <p class="text-sm text-gray-600 mt-1">A unique code for this item (e.g., 'CEM-001').</p>
                        </div>

                        <div>
                            <x-input-label for="item_name" :value="__('Item Name')" />
                            <x-text-input id="item_name" class="block mt-1 w-full" type="text" name="item_name" :value="old('item_name')" required autofocus />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="category" :value="__('Category (Optional)')" />
                            <x-text-input id="category" class="block mt-1 w-full" type="text" name="category" :value="old('category')" />
                            <p class="text-sm text-gray-600 mt-1">e.g., 'Cement', 'Piping', 'Electrical'</p>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="uom" :value="__('Unit of Measure (UoM)')" />
                            <x-text-input id="uom" class="block mt-1 w-full" type="text" name="uom" :value="old('uom')" required />
                            <p class="text-sm text-gray-600 mt-1">e.g., 'zak', 'm3', 'pcs', 'btg' (batang)</p>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="reorder_level" :value="__('Reorder Level (Optional)')" />
                            <x-text-input id="reorder_level" class="block mt-1 w-full" type="number" name="reorder_level" :value="old('reorder_level', 0)" />
                            <p class="text-sm text-gray-600 mt-1">Set a minimum stock quantity to trigger a low stock alert.</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('inventory-items.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ml-4">
                                {{ __('Save Item') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>