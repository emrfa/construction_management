<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Project Scheduler: {{ $project->quotation->project_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('projects.scheduler.store', $project) }}">
                        @csrf
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">Project Timeline</h3>
                            <p class="text-sm text-gray-500">
                                Overall Project Dates: 
                                <strong class="text-gray-700">{{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}</strong>
                                to
                                <strong class="text-gray-700">{{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}</strong>
                            </p>
                        </div>

                        <div class="border rounded-md">
                            {{-- Header --}}
                            <div class="grid grid-cols-12 gap-4 p-4 bg-gray-50 border-b font-semibold text-sm">
                                <div class="col-span-6">Work Breakdown Structure (WBS) Item</div>
                                <div class="col-span-3">Planned Start</div>
                                <div class="col-span-3">Planned End</div>
                            </div>

                            {{-- Recursive Item List --}}
                            <div class="space-y-2 p-4">
                                @foreach ($items as $item)
                                    @include('projects.partials.scheduler-item', ['item' => $item, 'level' => 0])
                                @endforeach
                            </div>
                        </div>

                        {{-- Form Actions --}}
                        <div class="flex items-center justify-end mt-6 border-t pt-4">
                            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                {{ __('Save Schedule') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>