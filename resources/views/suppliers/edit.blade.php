<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Supplier Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $supplier->name)" required autofocus />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="contact_person" :value="__('Contact Person (Optional)')" />
                            <x-text-input id="contact_person" class="block mt-1 w-full" type="text" name="contact_person" :value="old('contact_person', $supplier->contact_person)" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email (Optional)')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $supplier->email)" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Phone (Optional)')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $supplier->phone)" />
                        </div>

                         <div class="mt-4">
                            <x-input-label for="address" :value="__('Address (Optional)')" />
                            <textarea id="address" name="address" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', $supplier->address) }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ml-4">
                                {{ __('Update Supplier') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>