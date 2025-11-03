<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('AHS Details') }}: {{ $ahs_library->code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-bold">{{ $ahs_library->name }}</h3>
                            <p class="text-sm text-gray-500">Unit: <span class="font-medium text-gray-700">{{ $ahs_library->unit }}</span></p>
                            <p class="text-sm text-gray-500">Overhead/Profit: <span class="font-medium text-gray-700">{{ $ahs_library->overhead_profit_percentage }}%</span></p>
                            @if($ahs_library->notes)
                            <p class="text-sm text-gray-500 mt-2">Notes: <span class="font-medium text-gray-700">{{ $ahs_library->notes }}</span></p>
                            @endif
                        </div>
                        {{-- 1. REMOVED TOTAL COST FROM HEADER --}}
                        <div class="text-right flex-shrink-0 ml-4">
                            <a href="{{ route('ahs-library.edit', $ahs_library) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                Edit
                            </a>
                        </div>
                    </div>
                    
                    @php
                        // Calculate subtotals
                        $baseMaterialCost = $ahs_library->materials->sum(fn($i) => $i->coefficient * $i->unit_cost);
                        $baseLaborCost = $ahs_library->labors->sum(fn($i) => $i->coefficient * $i->rate);
                        $baseEquipmentCost = $ahs_library->equipments->sum(fn($i) => $i->coefficient * $i->cost_rate);
                        $baseTotalCost = $baseMaterialCost + $baseLaborCost + $baseEquipmentCost;
                    @endphp

                    <hr>

                    {{-- Materials --}}
                    <div>
                        <h4 class="font-semibold text-gray-800">Materials</h4>
                        {{-- 2. REMOVED SUBTOTAL FROM HERE --}}
                        <ul class="mt-2 list-disc list-inside space-y-1 text-sm">
                            @forelse($ahs_library->materials as $mat)
                                <li>
                                    {{ $mat->inventoryItem->item_code }} - {{ $mat->inventoryItem->name }}
                                    <span class="text-gray-600 ml-2">
                                        ({{ $mat->coefficient }} x Rp {{ number_format($mat->unit_cost, 2) }} = <span class="font-medium">Rp {{ number_format($mat->coefficient * $mat->unit_cost, 2) }}</span>)
                                    </span>
                                </li>
                            @empty
                                <li class="text-gray-500 italic">No materials assigned.</li>
                            @endforelse
                        </ul>
                    </div>
                    
                    {{-- Labors --}}
                    <div>
                        <h4 class="font-semibold text-gray-800">Labors</h4>
                        {{-- 2. REMOVED SUBTOTAL FROM HERE --}}
                        <ul class="mt-2 list-disc list-inside space-y-1 text-sm">
                            @forelse($ahs_library->labors as $lab)
                                <li>
                                    {{ $lab->laborRate->labor_type }}
                                    <span class="text-gray-600 ml-2">
                                        ({{ $lab->coefficient }} x Rp {{ number_format($lab->rate, 2) }} = <span class="font-medium">Rp {{ number_format($lab->coefficient * $lab->rate, 2) }}</span>)
                                    </span>
                                </li>
                            @empty
                                <li class="text-gray-500 italic">No labors assigned.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Equipments --}}
                    <div>
                        <h4 class="font-semibold text-gray-800">Equipments</h4>
                        {{-- 2. REMOVED SUBTOTAL FROM HERE --}}
                        <ul class="mt-2 list-disc list-inside space-y-1 text-sm">
                            @forelse($ahs_library->equipments as $eq)
                                <li>
                                    {{ $eq->equipment->name }}
                                    <span class="text-gray-600 ml-2">
                                        ({{ $eq->coefficient }} x Rp {{ number_format($eq->cost_rate, 2) }} = <span class="font-medium">Rp {{ number_format($eq->coefficient * $eq->cost_rate, 2) }}</span>)
                                    </span>
                                </li>
                            @empty
                                <li class="text-gray-500 italic">No equipments assigned.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- 2. ADDED SUBTOTALS TO THE FINAL SUMMARY BLOCK --}}
                    <div class="flex justify-end mt-4">
                         <div class="w-64 text-right border-t pt-4 space-y-1">
                            <p class="text-sm text-gray-600">Total Material Cost: <span class="font-semibold"">Rp {{ number_format($baseMaterialCost, 2) }}</span></p>
                            <p class="text-sm text-gray-600">Total Labor Cost: <span class="font-semibold"">Rp {{ number_format($baseLaborCost, 2) }}</span></p>
                            <p class="text-sm text-gray-600">Total Equipment Cost: <span class="font-semibold"">Rp {{ number_format($baseEquipmentCost, 2) }}</span></p>
                            <p class="text-sm text-gray-600 border-b pb-1 mb-1">Base Cost (Mat+Lab+Eq): <span class="font-semibold"">Rp {{ number_format($baseTotalCost, 2) }}</span></p>
                            <p class="text-sm text-gray-600">Overhead ({{ $ahs_library->overhead_profit_percentage }}%): <span class="font-semibold"">Rp {{ number_format($baseTotalCost * ($ahs_library->overhead_profit_percentage / 100), 2) }}</span></p>
                            <p class="text-lg font-bold mt-1 pt-1">Final Unit Cost: <span class="font-semibold"">Rp {{ number_format($ahs_library->total_cost, 2) }}</span></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>