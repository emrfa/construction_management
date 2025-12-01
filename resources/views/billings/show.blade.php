<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Billings', 'url' => route('billings.index')],
            ['label' => $billing->billing_no, 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
       <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">

                <strong class="text-gray-600">Project:</strong>
                <p class="text-lg">{{ $billing->project->quotation->project_name }}</p> 
                <a href="{{ route('projects.show', $billing->project) }}" class="text-indigo-600 hover:text-indigo-900">
                    &larr; {{ $billing->project->project_code }}
                </a>
                <span class="text-gray-500">/</span>
                <span>Billing {{ $billing->billing_no }}</span>
            </h2>

            <div>
                @if ($billing->status == 'pending')
                    <a href="{{ route('billings.edit', $billing) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Edit Billing
                    </a>
                @endif
                </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div>
                            <strong class="text-gray-600">Project:</strong>
                            <p class="text-lg">{{ $billing->project->project_code }}</p>
                        </div>

                        <div>
                            <strong class="text-gray-600">Project:</strong>
                            <p class="text-lg">{{ $billing->project->quotation->project_name }}</p> 
                            <p class="text-sm text-gray-500">{{ $billing->project->project_code }}</p> 
                        </div>

                        <div>
                            <strong class="text-gray-600">Billing Date:</strong>
                            <p class="text-lg">{{ \Carbon\Carbon::parse($billing->billing_date)->format('F j, Y') }}</p>
                        </div>
                        <div>
                            <strong class="text-gray-600">Amount (Rp):</strong>
                            <p class="text-lg font-semibold">{{ number_format($billing->amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <strong class="text-gray-600">Status:</strong>
                        <p>
                            <span class="font-medium px-2 py-0.5 rounded
                                @switch($billing->status)
                                    @case('pending') bg-yellow-200 text-yellow-800 @break
                                    @case('approved') bg-blue-200 text-blue-800 @break
                                    @case('invoiced') bg-green-200 text-green-800 @break
                                    @case('rejected') bg-red-200 text-red-800 @break
                                @endswitch
                            ">
                                {{ ucfirst($billing->status) }}
                            </span>
                        </p>
                    </div>

                    @if($billing->notes)
                    <div class="mb-6">
                        <strong class="text-gray-600">Notes:</strong>
                        <p class="mt-1 text-gray-700">{{ $billing->notes }}</p>
                    </div>
                    @endif

                    <div class="mt-6 border-t pt-4">
                        @if ($billing->status == 'pending')
                            <p class="text-sm text-gray-600 mb-2">This billing request is awaiting approval.</p>
                            <div class="flex space-x-2">
                                <form method="POST" action="{{ route('billings.updateStatus', $billing) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        Reject
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('billings.updateStatus', $billing) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        Approve
                                    </button>
                                </form>
                            </div>
                        @elseif ($billing->status == 'approved')
                            <p class="text-sm text-gray-600">This billing request is approved and ready to be invoiced.</p>
                            @elseif ($billing->status == 'invoiced')
                            <p class="text-sm text-gray-600">An invoice has been generated for this billing request.</p>
                            @elseif ($billing->status == 'rejected')
                            <p class="text-sm text-red-600">This billing request was rejected.</p>
                            <form method="POST" action="{{ route('billings.updateStatus', $billing) }}">
                                @csrf
                                <input type="hidden" name="status" value="pending">
                                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm mt-2">
                                    Re-open as Pending
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>