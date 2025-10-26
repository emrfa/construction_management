<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                 <a href="{{ route('ahs-library.index') }}" class="text-indigo-600 hover:text-indigo-900">
                    &larr; AHS Library
                </a>
                <span class="text-gray-500">/</span>
                <span>{{ $ahs_library->code }}</span>
            </h2>
            <a href="{{ route('ahs-library.edit', $ahs_library) }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                Edit AHS
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div class="border-b pb-4">
                        <h3 class="text-lg font-semibold">{{ $ahs_library->name }}</h3>
                        <p class="text-sm text-gray-600">Code: <span class="font-mono">{{ $ahs_library->code }}</span> | Unit: <span class="font-semibold">{{ $ahs_library->unit }}</span></p>
                         @if($ahs_library->notes)
                            <p class="mt-2 text-sm text-gray-700">{{ $ahs_library->notes }}</p>
                        @endif
                    </div>

                    <div>
                        <h4 class="font-semibold mb-2 text-gray-800">Materials</h4>
                        @if($ahs_library->materials->count() > 0)
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                @foreach($ahs_library->materials as $mat)
                                <li>
                                    <span class="font-medium">{{ $mat->inventoryItem->item_name }} ({{ $mat->inventoryItem->item_code }})</span>:
                                    {{ rtrim(rtrim(number_format($mat->coefficient, 4), '0'), '.') }}
                                    {{ $mat->inventoryItem->uom }}
                                    @ Rp {{ number_format($mat->unit_cost, 0, ',', '.') }}
                                    = <span class="font-semibold">Rp {{ number_format($mat->coefficient * $mat->unit_cost, 0, ',', '.') }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500">No materials defined.</p>
                        @endif
                    </div>

                     <div>
                        <h4 class="font-semibold mb-2 text-gray-800">Labor</h4>
                        @if($ahs_library->labors->count() > 0)
                             <ul class="list-disc list-inside space-y-1 text-sm">
                                @foreach($ahs_library->labors as $lab)
                                <li>
                                    <span class="font-medium">{{ $lab->laborRate->labor_type }}</span>:
                                    {{ rtrim(rtrim(number_format($lab->coefficient, 4), '0'), '.') }}
                                    {{ $lab->laborRate->unit }}
                                    @ Rp {{ number_format($lab->rate, 0, ',', '.') }}
                                    = <span class="font-semibold">Rp {{ number_format($lab->coefficient * $lab->rate, 0, ',', '.') }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @else
                             <p class="text-sm text-gray-500">No labor defined.</p>
                        @endif
                    </div>

                    <div class="border-t pt-4 text-right">
                         @php
                            $baseMaterialCost = $ahs_library->materials()->sum(DB::raw('coefficient * unit_cost'));
                            $baseLaborCost = $ahs_library->labors()->sum(DB::raw('coefficient * rate'));
                            $baseTotal = $baseMaterialCost + $baseLaborCost;
                            $overheadAmount = $baseTotal * ($ahs_library->overhead_profit_percentage / 100);
                         @endphp
                         <p class="text-sm text-gray-600">Material Cost: Rp {{ number_format($baseMaterialCost, 0, ',', '.') }}</p>
                         <p class="text-sm text-gray-600">Labor Cost: Rp {{ number_format($baseLaborCost, 0, ',', '.') }}</p>
                         <p class="text-sm text-gray-600 border-b pb-1 mb-1">Base Cost: <span class="font-semibold">Rp {{ number_format($baseTotal, 0, ',', '.') }}</span></p>
                         <p class="text-sm text-gray-600">Overhead/Profit ({{ $ahs_library->overhead_profit_percentage }}%): Rp {{ number_format($overheadAmount, 0, ',', '.') }}</p>
                        <p class="text-lg font-bold mt-1 pt-1">Final Unit Cost: Rp {{ number_format($ahs_library->total_cost, 0, ',', '.') }}</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>