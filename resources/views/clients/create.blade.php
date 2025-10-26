<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Client') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('clients.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            </div>

                        <div class="mt-4">
                            <x-input-label for="company_name" :value="__('Company Name (Optional)')" />
                            <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Phone (Optional)')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="address" :value="__('Address (Optional)')" />
                            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ml-4">
                                {{ __('Save Client') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>