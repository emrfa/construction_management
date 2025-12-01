<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Equipment', 'url' => route('equipment.index')],
            ['label' => 'New Equipment', 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Equipment Type/Record') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div x-data="{ status: '{{ old('status', 'pending_acquisition') }}' }">
                        <form method="POST" action="{{ route('equipment.store') }}">
                            @csrf

                            <h3 class="text-lg font-medium text-gray-900 mb-4">Equipment Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- Name --}}
                                <div class="md:col-span-2">
                                    <x-input-label for="name" :value="__('Equipment Name')" />
                                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                                {{-- Asset Code --}}
                                <div>
                                    <x-input-label for="identifier" :value="__('Asset Code (Optional)')" />
                                    <x-text-input id="identifier" class="block mt-1 w-full" type="text" name="identifier" :value="old('identifier')" placeholder="e.g., EXC-001" />
                                    <x-input-error :messages="$errors->get('identifier')" class="mt-2" />
                                </div>
                                {{-- Type --}}
                                <div>
                                    <x-input-label for="type" :value="__('Type / Category (Optional)')" />
                                    <x-text-input id="type" class="block mt-1 w-full" type="text" name="type" :value="old('type')" placeholder="e.g., Heavy Machinery"/>
                                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                                </div>
                                {{-- Status --}}
                                <div>
                                    <x-input-label for="status" :value="__('Initial Status')" />
                                    <select id="status" name="status" x-model="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="pending_acquisition">Pending Acquisition (via PO)</option>
                                        <option value="owned">Owned (Existing Asset)</option>
                                        <option value="rented">Rented</option>
                                        <option value="maintenance">Under Maintenance</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>
                            </div>

                            {{-- Conditional Fields for RENTED --}}
                            <div x-show="status === 'rented'" x-transition class="mt-6 border-t pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">External Rental Details</h3>
                                {{-- (All your existing rental fields go here) --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="md:col-span-3">
                                        <x-input-label for="supplier_id_rented" :value="__('Supplier')" />
                                        <select id="supplier_id_rented" name="supplier_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">Select Supplier...</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="rental_start_date" :value="__('Rental Start Date')" />
                                        <x-text-input id="rental_start_date" class="block mt-1 w-full" type="date" name="rental_start_date" :value="old('rental_start_date')" />
                                    </div>
                                    <div>
                                        <x-input-label for="rental_end_date" :value="__('Rental End Date')" />
                                        <x-text-input id="rental_end_date" class="block mt-1 w-full" type="date" name="rental_end_date" :value="old('rental_end_date')" />
                                    </div>
                                    <div class="flex items-end space-x-2">
                                        <div class="flex-1">
                                            <x-input-label for="rental_rate" :value="__('Actual Rental Rate (Rp)')" />
                                            <x-text-input id="rental_rate" class="block mt-1 w-full" type="number" step="0.01" name="rental_rate" :value="old('rental_rate')" placeholder="0.00"/>
                                        </div>
                                        <div class="w-24">
                                            <x-input-label for="rental_rate_unit" :value="__('Per')" />
                                            <select id="rental_rate_unit" name="rental_rate_unit" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                <option value="">Unit...</option>
                                                <option value="hour" {{ old('rental_rate_unit') == 'hour' ? 'selected' : '' }}>Hour</option>
                                                <option value="day" {{ old('rental_rate_unit') == 'day' ? 'selected' : '' }}>Day</option>
                                                <option value="week" {{ old('rental_rate_unit') == 'week' ? 'selected' : '' }}>Week</option>
                                                <option value="month" {{ old('rental_rate_unit') == 'month' ? 'selected' : '' }}>Month</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="md:col-span-3">
                                        <x-input-label for="rental_agreement_ref" :value="__('Agreement Ref (e.g., PO #, Optional)')" />
                                        <x-text-input id="rental_agreement_ref" class="block mt-1 w-full" type="text" name="rental_agreement_ref" :value="old('rental_agreement_ref')" />
                                    </div>
                                </div>
                            </div>

                            {{-- === FIX: Conditional Fields for OWNED or PENDING === --}}
                            <div x-show="status === 'owned' || status === 'pending_acquisition'" x-transition class="mt-6 border-t pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Internal Pricing (For AHS)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="base_purchase_price" :value="__('Purchase Price (Rp)')" />
                                        <x-text-input id="base_purchase_price" class="block mt-1 w-full" type="number" step="0.01" name="base_purchase_price" :value="old('base_purchase_price')" placeholder="0.00"/>
                                        <x-input-error :messages="$errors->get('base_purchase_price')" class="mt-2" />
                                    </div>
                                    <div class="flex items-end space-x-2">
                                        <div class="flex-1">
                                            <x-input-label for="base_rental_rate" :value="__('Internal Rental Rate (Rp)')" />
                                            <x-text-input id="base_rental_rate" class="block mt-1 w-full" type="number" step="0.01" name="base_rental_rate" :value="old('base_rental_rate')" placeholder="0.00"/>
                                            <x-input-error :messages="$errors->get('base_rental_rate')" class="mt-2" />
                                        </div>
                                        <div class="w-24">
                                            <x-input-label for="base_rental_rate_unit" :value="__('Per')" />
                                            <select id="base_rental_rate_unit" name="base_rental_rate_unit" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                <option value="">Unit...</option>
                                                <option value="hour" {{ old('base_rental_rate_unit') == 'hour' ? 'selected' : '' }}>Hour</option>
                                                <option value="day" {{ old('base_rental_rate_unit') == 'day' ? 'selected' : '' }}>Day</option>
                                                <option value="week" {{ old('base_rental_rate_unit') == 'week' ? 'selected' : '' }}>Week</option>
                                                <option value="month" {{ old('base_rental_rate_unit') == 'month' ? 'selected' : '' }}>Month</option>
                                            </select>
                                            <x-input-error :messages="$errors->get('base_rental_rate_unit')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- === END FIX === --}}

                            {{-- Notes --}}
                            <div class="mt-6 border-t pt-6">
                                 <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                 <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                 <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center justify-end mt-6 pt-6 border-t">
                                <a href="{{ route('equipment.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md mr-4">
                                    {{ __('Cancel') }}
                                </a>
                                <x-primary-button>
                                    {{ __('Save Equipment Record') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>