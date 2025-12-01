<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Work Types', 'url' => route('work-types.index')],
            ['label' => $work_type->name, 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- FIX: Changed $workType to $work_type --}}
            {{ __('Work Type') }}: {{ $work_type->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div>
                        {{-- FIX: Changed $workType to $work_type --}}
                        <a href="{{ route('work-types.edit', $work_type) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit Work Type
                        </a>
                    </div>

                    {{-- FIX: Changed $workType to $work_type --}}
                    @if($work_type->workItems->isNotEmpty())
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Work Items</h3>
                            <ul class="mt-2 list-disc list-inside space-y-1">
                                @foreach($work_type->workItems as $item)
                                    <li>{{ $item->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- FIX: Changed $workType to $work_type --}}
                    @if($work_type->unitRateAnalyses->isNotEmpty())
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">AHS</h3>
                            <ul class="mt-2 list-disc list-inside space-y-1">
                                @foreach($work_type->unitRateAnalyses as $ahs)
                                    <li>[{{ $ahs->code }}] - {{ $ahs->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- FIX: Changed $workType to $work_type --}}
                    @if($work_type->workItems->isEmpty() && $work_type->unitRateAnalyses->isEmpty())
                        <p class="text-gray-500">This Work Type has no child items or direct AHS links assigned.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>