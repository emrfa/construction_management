<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Equipment') }}: {{ $equipment->name }}
            </h2>
            <a href="{{ route('equipment.edit', $equipment) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                Edit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    {{-- Helper grid for cleaner layout --}}
                    @php
                        $detailClass = "mt-1 text-md text-gray-900 font-medium";
                        $labelClass = "text-sm text-gray-500";
                    @endphp

                    {{-- Main Details --}}
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Equipment Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <span class="{{ $labelClass }}">Name</span>
                                <p class="{{ $detailClass }}">{{ $equipment->name }}</p>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">Asset Code</span>
                                <p class="{{ $detailClass }}">{{ $equipment->identifier ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">Type</span>
                                <p class="{{ $detailClass }}">{{ $equipment->type ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">Status</span>
                                <p class="{{ $detailClass }}">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $equipment->status == 'owned' ? 'bg-green-100 text-green-800' : ($equipment->status == 'rented' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ Str::title(str_replace('_', ' ', $equipment->status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Internal Pricing --}}
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Price</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="{{ $labelClass }}">Base Purchase Price</span>
                                <p class="{{ $detailClass }}">Rp {{ number_format($equipment->base_purchase_price, 2) }}</p>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">Rental Rate</span>
                                <p class="{{ $detailClass }}">
                                    Rp {{ number_format($equipment->base_rental_rate, 2) }}
                                    @if($equipment->base_rental_rate_unit)
                                        / {{ $equipment->base_rental_rate_unit }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Ownership Details --}}
                    @if($equipment->status == 'owned' && $equipment->purchase_date)
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Ownership Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <span class="{{ $labelClass }}">Purchase Date</span>
                                <p class="{{ $detailClass }}">{{ $equipment->purchase_date->format('d M Y') }}</p>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">Purchase Cost</span>
                                <p class="{{ $detailClass }}">Rp {{ number_format($equipment->purchase_cost, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    {{-- External Rental Details --}}
                    @if($equipment->status == 'rented' && $equipment->supplier)
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">External Rental Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-3">
                                <span class="{{ $labelClass }}">Supplier</span>
                                <p class="{{ $detailClass }}">{{ $equipment->supplier->name }}</p>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">Rental Start</span>
                                <p class="{{ $detailClass }}">{{ $equipment->rental_start_date?->format('d M Y') ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">Rental End</span>
                                <p class="{{ $detailClass }}">{{ $equipment->rental_end_date?->format('d M Y') ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <span class="{{ $labelClass }}">Actual Rental Rate</span>
                                <p class="{{ $detailClass }}">
                                    Rp {{ number_format($equipment->rental_rate, 2) }}
                                    @if($equipment->rental_rate_unit)
                                        / {{ $equipment->rental_rate_unit }}
                                    @endif
                                </p>
                            </div>
                            <div class="md:col-span-3">
                                <span class="{{ $labelClass }}">Agreement Reference</span>
                                <p class="{{ $detailClass }}">{{ $equipment->rental_agreement_ref ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($equipment->notes)
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($equipment->notes)) !!}
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>