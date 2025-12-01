<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Work Types', 'url' => route('work-types.index')],
            ['label' => $work_type->name, 'url' => route('work-types.show', $work_type)],
            ['label' => 'Edit', 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- FIX: Changed $workType to $work_type --}}
            {{ __('Edit Work Type') }}: {{ $work_type->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- FIX: Changed $workType to $work_type --}}
                    <form method="POST" action="{{ route('work-types.update', $work_type) }}">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="name" :value="__('Work Type Name')" />
                            {{-- FIX: Changed $workType to $work_type --}}
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $work_type->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="work_items" :value="__('Child Work Items (if this is a Group)')" />
                            <select id="tom-select-work-items" name="work_items[]" multiple placeholder="Select work items...">
                                @foreach($allWorkItems as $item)
                                    <option value="{{ $item->id }}" {{ in_array($item->id, old('work_items', $selectedWorkItems)) ? 'selected' : '' }}>
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
                                    <option value="{{ $ahs->id }}" {{ in_array($ahs->id, old('ahs_items', $selectedAHS)) ? 'selected' : '' }}>
                                        [{{ $ahs->code }}] - {{ $ahs->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('ahs_items')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Work Type') }}
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