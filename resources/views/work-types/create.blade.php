<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Work Type') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('work-types.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Work Type Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="work_items" :value="__('Child Work Items (if this is a Group)')" />
                            <select id="tom-select-work-items" name="work_items[]" multiple placeholder="Select work items...">
                                @foreach($allWorkItems as $item)
                                    <option value="{{ $item->id }}" {{ in_array($item->id, old('work_items', [])) ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('work_items')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="ahs_items" :value="__('Direct AHS Links (if this is a Task)')" />
                            <select id="tom-select-ahs-items" name="ahs_items[]" multiple placeholder="Select AHS items...">
                                @foreach($allAHS as $ahs)
                                    <option value="{{ $ahs->id }}" {{ in_array($ahs->id, old('ahs_items', [])) ? 'selected' : '' }}>
                                        {{ $ahs->code }} - {{ $ahs->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('ahs_items')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Create Work Type') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new TomSelect('#tom-select-work-items', {
                plugins: ['remove_button'],
                create: false,
            });
            
            new TomSelect('#tom-select-ahs-items', {
                plugins: ['remove_button'],
                create: false,
            });
        });
    </script>
    @endpush
</x-app-layout>