<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Stock Location') }}: {{ $stockLocation->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" x-data="{ type: '{{ old('type', $stockLocation->type) }}' }">
                    <form method="POST" action="{{ route('stock-locations.update', $stockLocation) }}">
                        @csrf
                        @method('PATCH')
                        
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="name" :value="__('Location Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $stockLocation->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="code" :value="__('Location Code')" />
                                <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $stockLocation->code)" required />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="type" :value="__('Location Type')" />
                                <select id="type" name="type" x-model="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="warehouse" @selected(old('type', $stockLocation->type) == 'warehouse')>Warehouse (General)</option>
                                    <option value="site" @selected(old('type', $stockLocation->type) == 'site')>Project Site</option>
                                </select>
                            </div>
                            
                            <div x-show="type === 'site'" x-transition>
                                <x-input-label for="project_id" :value="__('Link to Project')" />
                                <select id="project_id" name="project_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Select a project...</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id', $stockLocation->project_id) == $project->id ? 'selected' : '' }}>
                                            {{ $project->project_code }} - {{ $project->quotation->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Only projects without a location (plus the current one) are shown.</p>
                                <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="address" :value="__('Address (Optional)')" />
                                <textarea id="address" name="address" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('address', $stockLocation->address) }}</textarea>
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                            <div class="block">
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" 
                                        {{ old('is_active', $stockLocation->is_active) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">{{ __('Active (this location can be used)') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 border-t pt-4">
                            <a href="{{ route('stock-locations.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Location') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>