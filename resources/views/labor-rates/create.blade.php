<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Labor Rate') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('labor-rates.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="labor_type" :value="__('Labor Type')" />
                            <x-text-input id="labor_type" class="block mt-1 w-full" type="text" name="labor_type" :value="old('labor_type')" required autofocus />
                            <p class="text-sm text-gray-600 mt-1">e.g., 'Tukang Batu', 'Mandor', 'Helper Proyek'</p>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="unit" :value="__('Unit')" />
                            <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit')" required />
                            <p class="text-sm text-gray-600 mt-1">e.g., 'Hari' (Day), 'Jam' (Hour), 'OH' (Orang Hari), 'OJ' (Orang Jam)</p>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="rate" :value="__('Rate (Rp)')" />
                            <x-text-input id="rate" class="block mt-1 w-full" type="number" step="0.01" name="rate" :value="old('rate')" required />
                            <p class="text-sm text-gray-600 mt-1">Cost per unit (e.g., 150000 for Rp 150.000)</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('labor-rates.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ml-4">
                                {{ __('Save Labor Rate') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>