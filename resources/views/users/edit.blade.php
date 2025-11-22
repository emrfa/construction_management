<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('New Password (Optional)')" />
                            <x-text-input id="password" class="block mt-1 w-full"
                                            type="password"
                                            name="password"
                                            autocomplete="new-password" />
                            <p class="text-xs text-gray-500 mt-1">Leave blank to keep the current password.</p>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                            type="password"
                                            name="password_confirmation" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="mt-6 border-t pt-6">
                            <label class="block font-medium text-lg text-gray-700">Roles</label>
                            
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                @forelse($roles as $role)
                                    <div class="flex items-center">
                                        <input id="role_{{ $role->id }}" 
                                               name="roles[]" 
                                               type="checkbox" 
                                               value="{{ $role->name }}"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                               {{ in_array($role->name, $userRoles) ? 'checked' : '' }}
                                               
                                               @if($role->name === 'Admin' && $user->email === 'admin@example.com')
                                                   disabled
                                               @endif
                                        >
                                        <label for="role_{{ $role->id }}" class="ml-2 block text-sm text-gray-900">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                @empty
                                    <p>No roles found.</p>
                                @endforelse
                            </div>
                            </div>
                        </div>

                        <div class="mt-6 border-t pt-6">
                            <label class="block font-medium text-lg text-gray-700 mb-4">Assigned Stock Locations</label>
                            
                            <div class="max-w-xl">
                                <select id="stock_locations" name="stock_locations[]" multiple placeholder="Select locations..." autocomplete="off">
                                    @foreach($stockLocations as $location)
                                        <option value="{{ $location->id }}" {{ in_array($location->id, $userLocationIds) ? 'selected' : '' }}>
                                            {{ $location->name }} ({{ $location->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-gray-500 mt-2">Users can only create transfers FROM these locations.</p>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                new TomSelect('#stock_locations', {
                                    plugins: ['remove_button'],
                                    create: false,
                                    sortField: { field: "text", direction: "asc" }
                                });
                            });
                        </script>

                        <div class="flex items-center justify-end mt-6 border-t pt-4">
                            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update User') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>