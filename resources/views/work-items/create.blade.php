<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Work Items', 'url' => route('work-items.index')],
            ['label' => 'New Work Item', 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Work Item to Library') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800 rounded-md">
                            <strong>Please correct the errors below:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('work-items.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">
                                Work Item Name
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                                   class="block w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="select-ahs" class="block font-medium text-sm text-gray-700">
                                AHS Components
                            </label>
                            <select name="ahs_items[]" id="select-ahs" multiple 
                                    placeholder="Search for AHS by code or name..."
                                    autocomplete="off"
                                    class="block w-full mt-1 rounded-md shadow-sm">
                                
                                @foreach ($allAHS as $ahs)
                                    <option value="{{ $ahs->id }}" {{ in_array($ahs->id, old('ahs_items', [])) ? 'selected' : '' }}>
                                        {{ $ahs->code }} - {{ $ahs->name }} ({{ $ahs->unit }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500">
                                This is the "recipe" for the Work Item.
                            </p>
                        </div>

                        <div class="flex items-center justify-end pt-4">
                            <a href="{{ route('work-items.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>

                            <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Save Work Item') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if TomSelect exists
            if (typeof TomSelect !== 'undefined') {
                new TomSelect('#select-ahs',{
                    plugins: ['remove_button'], // Adds the 'x' to remove items
                    create: false,
                    maxItems: 20,
                    // This makes the dropdown look nice
                    render: {
                        option: function(data, escape) {
                            const parts = escape(data.text).split(' - ');
                            const code = parts[0];
                            const rest = parts.length > 1 ? parts[1] : '';
                            
                            return `<div>
                                        <span class="font-semibold">${code}</span>
                                        <span class="text-gray-600"> - ${rest}</span>
                                    </div>`;
                        },
                        item: function(data, escape) {
                            return `<div>${escape(data.text)}</div>`;
                        }
                    }
                });
            } else {
                console.error('TomSelect is not loaded. Please check your app.blade.php file.');
            }
        });
    </script>
    @endpush
</x-app-layout>