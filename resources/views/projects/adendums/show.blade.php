<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Projects', 'url' => route('projects.index')],
            ['label' => $project->project_code, 'url' => route('projects.show', $project)],
            ['label' => 'Adendums', 'url' => route('projects.adendums.index', $project)],
            ['label' => $adendum->adendum_no, 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Adendum Details: ') }} {{ $adendum->adendum_no }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Header Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Date</p>
                            <p class="font-medium">{{ $adendum->date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $adendum->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                   ($adendum->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ strtoupper($adendum->status) }}
                            </span>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Subject</p>
                            <p class="font-medium text-lg">{{ $adendum->subject }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Description</p>
                            <p class="whitespace-pre-wrap">{{ $adendum->description ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Time Extension</p>
                            <p class="font-medium">{{ $adendum->time_extension_days }} Days</p>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200">

                    {{-- Items Table --}}
                    <h3 class="text-lg font-medium mb-4">Items</h3>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">UoM</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($adendum->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $item->description }}
                                            @if($item->quotation_item_id)
                                                <span class="text-xs text-gray-500 block">(Linked to Original Item)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                            {{ number_format($item->quantity, 4) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-center">
                                            {{ $item->uom }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                            {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">
                                            {{ number_format($item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 font-bold">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right">Total Amount:</td>
                                    <td class="px-6 py-4 text-right">
                                        {{ number_format($adendum->total_amount, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-between items-center">
                        <a href="{{ route('projects.adendums.index', $project) }}" class="text-gray-600 hover:text-gray-900 underline">
                            &larr; Back to List
                        </a>

                        @if($adendum->status === 'draft')
                            <form action="{{ route('projects.adendums.approve', [$project, $adendum]) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this adendum? This action cannot be undone.');">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                    Approve Adendum
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
