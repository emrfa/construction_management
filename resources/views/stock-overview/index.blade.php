<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Stock Overview by Location') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <p class="text-sm text-gray-600">Please select a location to view its detailed inventory.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse ($locations as $location)
                            <a href="{{ route('stock-overview.show', $location) }}" class="block p-4 bg-gray-50 border rounded-lg hover:bg-white hover:shadow-md transition">
                                <div class="font-semibold text-indigo-700">
                                    {{ $location->name }}
                                </div>
                                <div class="text-sm text-gray-500 font-mono">{{ $location->code }}</div>
                                @if($location->type == 'site' && $location->project)
                                    <div class="text-xs text-gray-600 mt-2">
                                        Project: {{ $location->project->quotation->project_name }}
                                    </div>
                                @endif
                            </a>
                        @empty
                            <div class="p-4 text-gray-500">
                                No active stock locations found. Please create one in the "Stock Locations" menu.
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>