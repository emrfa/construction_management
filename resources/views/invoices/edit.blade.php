<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; Invoice {{ $invoice->invoice_no }}
            </a>
            <span class="text-gray-500">/</span>
            <span>Edit Invoice</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="issued_date" :value="__('Issued Date')" />
                            <x-text-input id="issued_date" class="block mt-1 w-full bg-gray-100" type="date" name="issued_date" :value="$invoice->issued_date" readonly />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="due_date" :value="__('Due Date')" />
                            <x-text-input id="due_date" class="block mt-1 w-full" type="date" name="due_date" :value="old('due_date', $invoice->due_date)" />
                        </div>

                        <div class="mt-4">
                            <x-input-label :value="__('Amount (Rp)')" />
                            <p class="mt-1 text-lg font-semibold">{{ number_format($invoice->amount, 0, ',', '.') }}</p>
                         </div>
                         <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('invoices.show', $invoice) }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button class="ml-4">
                                {{ __('Update Invoice') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>