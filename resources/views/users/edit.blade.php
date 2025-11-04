<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Roles for') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-4">
                        <p><strong>Name:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                    </div>

                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-4">
                            <label class="block font-medium text-sm text-gray-700">Roles</label>
                            
                            @forelse($roles as $role)
                                <div class="flex items-center">
                                    <input id="role_{{ $role->id }}" 
                                           name="roles[]" 
                                           type="checkbox" 
                                           value="{{ $role->name }}" 
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                           {{ in_array($role->name, $userRoles) ? 'checked' : '' }}
                                    >
                                    <label for="role_{{ $role->id }}" class="ml-2 block text-sm text-gray-900">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No roles have been created yet. Run `php artisan tinker` to create them.</p>
                            @endforelse
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Roles') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>