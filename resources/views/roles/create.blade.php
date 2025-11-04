<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Role') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('roles.store') }}">
                        @csrf
                        
                        <div>
                            <x-input-label for="name" :value="__('Role Name')" />
                            <x-text-input id="name" class="block mt-1 w-full md:w-1/2" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-6">
                            <label class="block font-medium text-lg text-gray-700">Permissions</label>
                            
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @forelse($permissions as $group => $perms)
                                    <div class="space-y-2">
                                        <h4 class="font-semibold text-gray-800 capitalize border-b pb-1">{{ $group }}</h4>
                                        <div class="space-y-1">
                                            @foreach($perms as $permission)
                                                <div class="flex items-center">
                                                    <input id="perm_{{ $permission->id }}" 
                                                           name="permissions[]" 
                                                           type="checkbox" 
                                                           value="{{ $permission->name }}"
                                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                    <label for="perm_{{ $permission->id }}" class="ml-2 block text-sm text-gray-900">
                                                        {{ $permission->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <p>No permissions found.</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 border-t pt-4">
                            <a href="{{ route('roles.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Save Role') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>