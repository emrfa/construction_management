<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="{{ route('quotations.index') }}" class="text-indigo-600 hover:text-indigo-900">
                    &larr; All Quotations
                </a>
                <span class="text-gray-500">/</span>
                <span>{{ $quotation->quotation_no }}</span>
            </h2>

            <div class="flex items-center space-x-2">

                @if ($quotation->status == 'draft')
                    <a href="{{ route('quotations.edit', $quotation) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Edit Quotation
                    </a>
                @endif

                @if ($quotation->project)

                    <span class="px-4 py-2 bg-green-200 text-green-800 rounded-md font-semibold text-sm">
                        Project Created
                    </span>
                    <a href="{{ route('projects.show', $quotation->project) }}" class="bg-gray-700 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded">
                        View Project
                    </a>

                @else

                    @if ($quotation->status == 'draft')
                        <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                            @csrf
                            <input type="hidden" name="status" value="sent">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Mark as Sent
                            </button>
                        </form>
                    @elseif ($quotation->status == 'sent')
                        <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}" class="inline-block">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Mark as Rejected
                            </button>
                        </form>
                        <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}" class="inline-block">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Mark as Approved & Create Project
                            </button>
                        </form>
                    @elseif ($quotation->status == 'rejected')
                        <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                            @csrf
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Re-open as Draft
                            </button>
                        </form>
                    @endif

                @endif

            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                
                <div class="p-6 text-gray-900">

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div>
                            <strong class="text-gray-600">Client:</strong>
                            <p class="text-lg">{{ $quotation->client->name }}</p>
                            <p>{{ $quotation->client->company_name }}</p>
                        </div>
                        <div>
                            <strong class="text-gray-600">Project:</strong>
                            <p class="text-lg">{{ $quotation->project_name }}</p>
                            @if($quotation->location)
                                <p class="text-sm text-gray-500">{{ $quotation->location }}</p>
                            @endif
                        </div>
                        <div>
                            <strong class="text-gray-600">Quote #:</strong>
                            <p class="text-lg">{{ $quotation->quotation_no }}</p>
                            <p class="text-sm"><strong>Date:</strong> {{ \Carbon\Carbon::parse($quotation->date)->format('F j, Y') }}</p>
                            <p class="text-sm"><strong>Status:</strong> <span class="font-medium px-2 py-0.5 rounded
                                @switch($quotation->status)
                                    @case('draft') bg-gray-200 text-gray-800 @break
                                    @case('sent') bg-blue-200 text-blue-800 @break
                                    @case('approved') bg-green-200 text-green-800 @break
                                    @case('rejected') bg-red-200 text-red-800 @break
                                @endswitch
                            ">{{ ucfirst($quotation->status) }}</span></p>
                        </div>
                    </div>

                    <hr class="my-6">

                    <h3 class="text-lg font-semibold mb-2">Quotation Items</h3>
                    
                    <div class="grid grid-cols-12 gap-2 text-sm font-bold text-gray-600 uppercase px-2 py-1 border-b">
                        <div class="col-span-5">Description</div>
                        <div class="col-span-1">Code</div>
                        <div class="col-span-1">UoM</div>
                        <div class="col-span-1 text-right">Qty</div>
                        <div class="col-span-2 text-right">Unit Price (Rp)</div>
                        <div class="col-span-2 text-right">Subtotal (Rp)</div>
                    </div>

                    <div class="space-y-1">
                        @foreach ($quotation->items as $item)
                            @include('quotations.partials.item-row', ['item' => $item, 'level' => 0, 'showProgress' => false])
                        @endforeach
                    </div>

                    <div class="flex justify-end mt-6">
                        <div class="w-64">
                            <div class="flex justify-between font-bold text-lg border-t-2 pt-2">
                                <span>Total Estimate:</span>
                                <span>Rp {{ number_format($quotation->total_estimate, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                </div> <div class="p-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Quotation History</h3>
                    <div class="space-y-3 max-h-60 overflow-y-auto border rounded-md p-4 bg-gray-50">
                        
                        @forelse($quotation->activities as $activity)
                            <div class="text-sm">
                                <p class="text-gray-800">
                                    <span class="font-bold">{{ $activity->causer->name ?? 'System' }}</span>
                                    {{ $activity->description }}
                                </p>
                                
                                @if($activity->description === 'updated' && $activity->properties->has('old'))
                                    <ul class="mt-1 pl-5 list-disc text-xs text-gray-500">
                                        @foreach($activity->properties['old'] as $key => $value)
                                            <li>
                                                Changed <span class="font-medium">{{ $key }}</span>
                                                from "<span class="text-red-500">{{ $value }}</span>"
                                                to "<span class="text-green-500">{{ $activity->properties['attributes'][$key] }}</span>"
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $activity->created_at->format('M d, Y @ h:i A') }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No history found for this quotation.</p>
                        @endforelse
                    </div>
                </div> </div>
        </div>
    </div>
</x-app-layout>