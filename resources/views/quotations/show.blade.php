<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Quotations', 'url' => route('quotations.index')],
            ['label' => $quotation->quotation_no, 'url' => '']
        ]" />
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-8">
            
            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row justify-between items-start gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $quotation->project_name }}</h1>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold border
                            @switch($quotation->status)
                                @case('draft') bg-gray-50 text-gray-600 border-gray-200 @break
                                @case('sent') bg-blue-50 text-blue-700 border-blue-200 @break
                                @case('approved') bg-emerald-50 text-emerald-700 border-emerald-200 @break
                                @case('rejected') bg-red-50 text-red-700 border-red-200 @break
                            @endswitch">
                            {{ ucfirst($quotation->status) }}
                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $quotation->client->name }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $quotation->location ?? 'No Location' }}
                        </span>
                        <span class="font-mono text-gray-400">#{{ $quotation->quotation_no }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    @if ($quotation->status == 'draft')
                        <a href="{{ route('quotations.edit', $quotation) }}" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                            @csrf
                            <input type="hidden" name="status" value="sent">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold shadow-sm hover:bg-indigo-700 transition">
                                Mark as Sent
                            </button>
                        </form>
                    @elseif ($quotation->status == 'sent')
                        <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                            @csrf
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold shadow-sm hover:bg-emerald-700 transition">
                                Approve
                            </button>
                        </form>
                        <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="px-4 py-2 bg-white border border-red-200 text-red-600 rounded-xl text-sm font-semibold shadow-sm hover:bg-red-50 transition">
                                Reject
                            </button>
                        </form>
                    @elseif ($quotation->status == 'rejected')
                        <form method="POST" action="{{ route('quotations.updateStatus', $quotation) }}">
                            @csrf
                            <input type="hidden" name="status" value="draft">
                            <button type="submit" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                Re-open as Draft
                            </button>
                        </form>
                    @endif

                    @if ($quotation->project)
                        <a href="{{ route('projects.show', $quotation->project) }}" class="px-4 py-2 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-xl text-sm font-semibold hover:bg-indigo-100 transition">
                            View Project
                        </a>
                    @endif

                    <a href="{{ route('quotations.export', $quotation) }}" target="_blank" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export PDF
                    </a>
                </div>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500">Total Estimate</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">Rp {{ number_format($quotation->total_estimate, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500">Quotation Date</p>
                    <p class="text-lg font-semibold text-gray-900 mt-2">{{ \Carbon\Carbon::parse($quotation->date)->format('d M Y') }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500">Total Items</p>
                    <p class="text-lg font-semibold text-gray-900 mt-2">{{ $quotation->items->count() }} Items</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500">Client Company</p>
                    <p class="text-lg font-semibold text-gray-900 mt-2 truncate">{{ $quotation->client->company_name ?? '-' }}</p>
                </div>
            </div>

            {{-- BOQ Table --}}
            <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-gray-100">
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-gray-900 text-lg">Bill of Quantities</h3>
                    <span class="text-sm text-gray-500">Breakdown of costs</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-8 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-5/12">Description</th>
                                <th scope="col" class="px-4 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/12">Code</th>
                                <th scope="col" class="px-4 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-1/12">Unit</th>
                                <th scope="col" class="px-4 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-1/12">Qty</th>
                                <th scope="col" class="px-4 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-2/12">Unit Price</th>
                                <th scope="col" class="px-8 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-2/12">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($originalItems as $item)
                                @include('quotations.partials.item-row', ['item' => $item, 'level' => 0])
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-8 py-4 text-right text-sm font-medium text-gray-500">Total Estimate</td>
                                <td class="px-8 py-4 text-right text-lg font-bold text-indigo-600">Rp {{ number_format($quotation->total_estimate, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- History Section --}}
            <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden" x-data="{ open: false }">
                <button @click="open = !open" class="w-full px-8 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Activity History</h4>
                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-xs font-medium text-gray-600">{{ $quotation->activities->count() }}</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7 7" />
                    </svg>
                </button>
                
                <div x-show="open" x-collapse class="border-t border-gray-100 bg-gray-50/30 p-8">
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
                                                @elseif($activity->description == 'approved') bg-emerald-100
                                                @elseif($activity->description == 'rejected') bg-red-100
                                                @else bg-gray-100 @endif">
                                                <svg class="h-4 w-4 
                                                    @if($activity->description == 'created') text-gray-500
                                                    @elseif($activity->description == 'updated') text-blue-500
                                                    @elseif($activity->description == 'approved') text-emerald-500
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
                                                    <div class="mt-2 text-xs text-gray-500 bg-white p-2 rounded border border-gray-200 shadow-sm">
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
</x-app-layout>