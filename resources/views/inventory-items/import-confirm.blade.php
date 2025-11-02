<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Confirm Inventory Import') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-300 rounded-md">
                        <h3 class="font-bold text-yellow-800">Review Required</h3>
                        <p class="text-sm text-yellow-700">We found category names in your file that don't exist in the database. Please review them and choose an action.</p>
                    </div>

                    <form method="POST" action="{{ route('inventory-items.import.process') }}">
                        @csrf
                        
                        <div class="space-y-4">
                            @foreach($problems as $index => $problem)
                                <div class="p-4 border rounded-md bg-gray-50">
                                    <h4 class="font-semibold">Problem: <span class="text-red-600 font-bold">"{{ $problem['name'] }}"</span></h4>

                                    <div class="mt-2">
                                        <label for="resolution_{{ $index }}" class="text-sm font-medium">Action:</label>
                                        <select name="resolutions[{{ $problem['name'] }}]" id="resolution_{{ $index }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="skip">Skip rows with this category</option>
                                            
                                            {{-- "Create New" option --}}
                                            <option value="create_new">Create new category: "{{ $problem['name'] }}"</option>

                                            {{-- "Did you mean?" suggestions --}}
                                            @if(!empty($problem['suggestions']))
                                                <optgroup label="Did you mean?">
                                                    @foreach($problem['suggestions'] as $suggestion)
                                                        <option value="Use: {{ $suggestion }}">Use: "{{ $suggestion }}"</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                            
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end mt-6 border-t pt-4">
                            <a href="{{ route('inventory-items.importForm') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel and Upload New File') }}
                            </a>
                            <x-primary-button>
                                {{ __('Process Import with These Fixes') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>