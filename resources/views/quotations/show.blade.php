<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Quotation Details') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('quotations.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Main Card --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200">
                
                {{-- Card Header: Project Info & Actions --}}
                <div class="p-8 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                        
                        {{-- Left: Title & Meta --}}
                        <div class="space-y-4 flex-1">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border
                                        @switch($quotation->status)
                                            @case('draft') bg-gray-50 text-gray-600 border-gray-200 @break
                                            @case('sent') bg-blue-50 text-blue-700 border-blue-200 @break
                                            @case('approved') bg-green-50 text-green-700 border-green-200 @break
                                            @case('rejected') bg-red-50 text-red-700 border-red-200 @break
                                        @endswitch">
                                        {{ ucfirst($quotation->status) }}
                                    </span>
                                    <span class="text-sm text-gray-500 font-mono">{{ $quotation->quotation_no }}</span>
                                </div>
                                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $quotation->project_name }}</h1>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Client</label>
                                    <div class="font-semibold text-gray-900">{{ $quotation->client->name }}</div>
                                    <div class="text-gray-500">{{ $quotation->client->company_name }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Location</label>
                                    <div class="font-medium text-gray-900">{{ $quotation->location ?? '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Date</label>
                                    <div class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($quotation->date)->format('F j, Y') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Actions --}}
                        <div class="flex flex-col gap-3 min-w-[140px]">
                            @if ($quotation->status == 'draft')
                                <a href="{{ route('quotations.edit', $quotation) }}" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="-ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                    Edit Quote
                                </a>
                                <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="sent">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Mark Sent
                                    </button>
                                </form>
                            @elseif ($quotation->status == 'sent')
                                <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        Reject
                                    </button>
                                </form>
                            @elseif ($quotation->status == 'rejected')
                                <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="draft">
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Re-open
                                    </button>
                                </form>
                            @endif

                            @if ($quotation->project)
                                <a href="{{ route('projects.show', $quotation->project) }}" class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View Project
                                </a>
                            @endif

                            <a href="{{ route('quotations.export', $quotation) }}" target="_blank" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l4 4a1 1 0 01.586 1.414V19a2 2 0 01-2 2z" />
                                </svg>
                                Export PDF
                            </a>
                        </div>
                    </div>
                </div>

                {{-- BOQ Section --}}
                <div class="p-8 border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-gray-900 text-lg">Bill of Quantities</h3>
                    </div>
                    
                    <div class="overflow-hidden ring-1 ring-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-5/12">Description</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/12">Code</th>
                                    <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-1/12">Unit</th>
                                    <th scope="col" class="px-3 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-1/12">Qty</th>
                                    <th scope="col" class="px-3 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-2/12">Unit Price</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-2/12">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($quotation->items as $item)
                                    @include('quotations.partials.item-row', ['item' => $item, 'level' => 0])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Totals --}}
                <div class="px-8 py-6 bg-gray-50 border-b border-gray-200">
                    <div class="flex justify-end">
                        <div class="w-full md:w-1/2 lg:w-1/3 space-y-2">
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-medium">Rp {{ number_format($quotation->total_estimate, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-600">
                                <span>Tax (0%)</span>
                                <span class="font-medium">-</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                                <span class="text-base font-bold text-gray-900">Grand Total</span>
                                <span class="text-2xl font-bold text-indigo-600 tracking-tight">Rp {{ number_format($quotation->total_estimate, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- History Section (Collapsible) --}}
                <div class="px-8 py-6 bg-white" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center justify-between w-full text-left focus:outline-none group">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">History</h4>
                        <span class="text-xs text-gray-500 group-hover:text-indigo-600 flex items-center gap-1">
                            <span x-text="open ? 'Hide' : 'Show'">Show</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7 7" />
                            </svg>
                        </span>
                    </button>
                    
                    <div x-show="open" x-collapse class="mt-6 flow-root">
                        <ul role="list" class="-mb-8">
                            @forelse($quotation->activities as $activity)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                    @if($activity->description == 'created') bg-gray-100
                                                    @elseif($activity->description == 'updated') bg-blue-100
                                                    @elseif($activity->description == 'approved') bg-green-100
                                                    @elseif($activity->description == 'rejected') bg-red-100
                                                    @else bg-gray-100 @endif">
                                                    <svg class="h-4 w-4 
                                                        @if($activity->description == 'created') text-gray-500
                                                        @elseif($activity->description == 'updated') text-blue-500
                                                        @elseif($activity->description == 'approved') text-green-500
                                                        @elseif($activity->description == 'rejected') text-red-500
                                                        @else text-gray-500 @endif" 
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        @if($activity->description == 'created')
                                                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 001-1l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                                                        @elseif($activity->description == 'updated')
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                        @elseif($activity->description == 'approved')
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        @elseif($activity->description == 'rejected')
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        @else
                                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                        @endif
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        {{ ucfirst($activity->description) }} by <span class="font-medium text-gray-900">{{ $activity->causer->name ?? 'System' }}</span>
                                                    </p>
                                                    @if($activity->description === 'updated' && $activity->properties->has('old'))
                                                        <div class="mt-2 text-xs text-gray-500 bg-gray-50 p-2 rounded border border-gray-100">
                                                            <ul class="list-disc pl-4 space-y-1">
                                                                @foreach($activity->properties['old'] as $key => $value)
                                                                    <li>
                                                                        Changed <span class="font-medium">{{ $key }}</span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time datetime="{{ $activity->created_at }}">{{ $activity->created_at->format('M d, Y') }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-sm text-gray-500 italic">No history recorded.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>