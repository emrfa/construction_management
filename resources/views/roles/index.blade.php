<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
                <p class="text-sm text-gray-500 mt-1">Define user roles and permissions.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('roles.create') }}" class="px-4 py-2 bg-indigo-600 rounded-xl text-white text-sm font-semibold shadow hover:bg-indigo-700 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Create Role
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="pageHandler">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-6">
            <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-2xl p-6 border border-gray-100">
                <form method="GET" action="{{ route('roles.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="search" class="text-sm font-medium text-gray-700">Search Roles</label>
                            <x-text-input type="text" name="search" id="search" class="mt-1 w-full rounded-xl border-gray-300" placeholder="Search by role name..." value="{{ request('search') }}"/>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm shadow hover:bg-indigo-700">Search</button>
                            <a href="{{ route('roles.index') }}" class="px-4 py-2 bg-white border rounded-xl text-sm text-gray-700 hover:bg-gray-50 shadow-sm">Clear</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Permissions</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($roles as $role)
                                <tr class="hover:bg-gray-50/60 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 align-top">{{ $role->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 align-top">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($role->permissions->pluck('name') as $permission)
                                                <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">{{ $permission }}</span>
                                            @empty
                                                <span class="text-xs text-gray-400">No permissions</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex justify-end gap-3 align-top">
                                        <a href="{{ route('roles.edit', $role) }}" class="text-gray-500 hover:text-indigo-600" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                        @if($role->name != 'Admin')
                                            <button type="button" @click="itemId = {{ $role->id }}; $dispatch('open-modal', 'confirm-delete')" class="text-gray-500 hover:text-red-600" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-gray-500 text-sm">No roles found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t bg-gray-50">{{ $roles->appends(request()->query())->links() }}</div>
            </div>
            
            <x-modal name="confirm-delete" focusable>
                <form method="post" x-bind:action="`/roles/${itemId}`" class="p-6">
                    @csrf @method('delete')
                    <h2 class="text-lg font-medium text-gray-900">{{ __('Delete Role?') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ __('This cannot be undone.') }}</p>
                    <div class="mt-6 flex justify-end">
                        <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                        <x-danger-button class="ml-3">{{ __('Delete') }}</x-danger-button>
                    </div>
                </form>
            </x-modal>
        </div>
    </div>
    @push('scripts') <script> document.addEventListener('alpine:init', () => { Alpine.data('pageHandler', () => ({ itemId: null })); }); </script> @endpush
</x-app-layout>