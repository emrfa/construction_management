<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Billings', 'url' => route('billings.index')],
            ['label' => $billing->billing_no, 'url' => route('billings.show', $billing)],
            ['label' => 'Edit', 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('billings.show', $billing) }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; Billing {{ $billing->billing_no }}
            </a>
            <span class="text-gray-500">/</span>
            <span>Edit Billing Request</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('billings.update', $billing) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="billing_date" :value="__('Billing Date')" />
                            <x-text-input id="billing_date" class="block mt-1 w-full" type="date" name="billing_date" :value="old('billing_date', $billing->billing_date)" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="amount" :value="__('Amount (Rp)')" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" name="amount" :value="old('amount', $billing->amount)" required />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="notes" :value="__('Notes (Optional)')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $billing->notes) }}</textarea>
                            <p class="text-sm text-gray-600 mt-1">e.g., "Milestone 1: Foundation Complete", "Monthly Progress Billing - October"</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('billings.show', $billing) }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ml-4">
                                {{ __('Update Billing Request') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>