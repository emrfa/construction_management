<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="{{ route('projects.index') }}" class="text-indigo-600 hover:text-indigo-900">
                &larr; All Projects
            </a>
            <span class="text-gray-500">/</span>
            <span>{{ $project->project_code }}</span>
        </h2>
    </x-slot>
    
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    

                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-2xl font-bold text-gray-900">{{ $project->quotation->project_name }}</h3>
                                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                                        @switch($project->status)
                                            @case('initiated') bg-blue-100 text-blue-800 @break
                                            @case('in_progress') bg-yellow-100 text-yellow-800 @break
                                            @case('completed') bg-green-100 text-green-800 @break
                                            @case('closed') bg-gray-200 text-gray-800 @break
                                        @endswitch">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">{{ $project->project_code }}</p>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <a href="{{ route('reports.material_flow', $project) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                    📊 Material Flow
                                </a>
                                
                                @if($project->isReadyForReport())
                                    <a href="{{ route('reports.project_performance', $project) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                        📈 Performance
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed" title="Project must be In Progress and have a Schedule to view report">
                                        📈 Performance (Not Ready)
                                    </span>
                                @endif
                                
                                @if ($project->status == 'in_progress')
                                    <form method="POST" action="{{ route('projects.complete', $project) }}" onsubmit="return confirm('Are you sure you want to mark this project as completed?');" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-green-700 transition">
                                            Mark Complete
                                        </button>
                                    </form>
                                @elseif ($project->status == 'completed')
                                    <form method="POST" action="{{ route('projects.close', $project) }}" onsubmit="return confirm('Are you sure you want to close this project? This action might restrict further changes.');" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-gray-700 transition">
                                            Close Project
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('projects.update', $project) }}" id="project-update-form">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                                {{-- Column 1: Details --}}
                                <div class="space-y-4">
                                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Project Details</h4>
                                    <div>
                                        <span class="block text-xs text-gray-400">Client</span>
                                        <p class="font-medium text-gray-900">{{ $project->client->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $project->client->company_name }}</p>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-gray-400">Location</span>
                                        @if(empty($project->location) && $project->status === 'initiated')
                                            <input type="text" name="location" 
                                                value="{{ old('location') }}" 
                                                class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Enter location">
                                        @else
                                            <p class="font-medium text-gray-900">{{ $project->location ?? 'Not specified' }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Column 2: Timeline --}}
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Timeline</h4>
                                        @if ($project->status === 'initiated')
                                            <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Save Dates</button>
                                        @endif
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_date" class="block text-xs text-gray-400 mb-1">Start Date</label>
                                            @if ($project->status === 'initiated')
                                                <input type="date" name="start_date" id="start_date" 
                                                    value="{{ old('start_date', $project->start_date) }}"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            @else
                                                <p class="font-medium text-gray-900">
                                                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : '-' }}
                                                </p>
                                            @endif
                                        </div>
                                        <div>
                                            <label for="end_date" class="block text-xs text-gray-400 mb-1">End Date</label>
                                            @if ($project->status === 'initiated')
                                                <input type="date" name="end_date" id="end_date" 
                                                    value="{{ old('end_date', $project->end_date) }}"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            @else
                                                <p class="font-medium text-gray-900">
                                                    {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y') : '-' }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if($project->actual_end_date)
                                        <div>
                                            <span class="block text-xs text-gray-400">Actual Completion</span>
                                            <p class="font-medium text-green-600">
                                                {{ \Carbon\Carbon::parse($project->actual_end_date)->format('M d, Y') }}
                                            </p>
                                        </div>
                                    @endif

                                    <div class="pt-2">
                                        <a href="{{ route('projects.scheduler', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            View Schedule
                                        </a>
                                    </div>
                                </div>

                                {{-- Column 3: Financials --}}
                                <div class="space-y-4">
                                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Financials</h4>
                                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Total Budget</span>
                                            <span class="font-semibold text-gray-900">Rp {{ number_format($project->total_budget, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Actual Cost</span>
                                            <span class="font-semibold text-orange-600">Rp {{ number_format($project->actual_cost, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="border-t border-gray-200 pt-2 flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-900">Remaining</span>
                                            <span class="font-bold {{ $project->budget_left >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                Rp {{ number_format($project->budget_left, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                    <hr class="my-6">

                    <div class="mt-6 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold">Material Requests</h3>
                            {{-- Link to the create form, passing the project ID --}}
                            @if(!in_array($project->status, ['completed', 'closed']))
                                <a href="{{ route('material-requests.create', ['project_id' => $project->id]) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    + Request Materials
                                </a>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Req #</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required Date</th> 
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($project->materialRequests as $request)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            <a href="{{ route('material-requests.show', $request) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                {{ $request->request_code }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($request->request_date)->format('m/d/Y') }}</td>
                                        {{-- Correct Data: Display requester name --}}
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $request->requester->name ?? 'N/A' }}</td>
                                        {{-- New Data Cell: Display required date --}}
                                        <td class="px-4 py-2 whitespace-nowrap">{{ $request->required_date ? \Carbon\Carbon::parse($request->required_date)->format('m/d/Y') : '-' }}</td>
                                        <td class="px-4 py-2 whitespace-nowrap">
                                            {{-- Status Badge --}}
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @switch($request->status)
                                                    @case('draft') bg-gray-200 text-gray-800 @break
                                                    @case('pending_approval') bg-yellow-200 text-yellow-800 @break
                                                    @case('approved') bg-green-200 text-green-800 @break
                                                    @case('rejected') bg-red-200 text-red-800 @break
                                                    @case('partially_fulfilled') bg-blue-200 text-blue-800 @break
                                                    @case('fulfilled') bg-purple-200 text-purple-800 @break
                                                    @case('cancelled') bg-gray-400 text-gray-800 @break
                                                @endswitch">
                                                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('material-requests.show', $request) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        {{-- Correct Colspan for 6 columns --}}
                                        <td colspan="6" class="px-4 py-2 text-center text-gray-500">
                                            No material requests created for this project yet.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr class="my-6">

                    <div class="mt-6 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold">Billing Requests</h3>
                            @if($project->status !== 'closed')
                                <a href="{{ route('billings.create', ['project_id' => $project->id]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    + Create Billing
                                </a>
                            @endif
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing #</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (Rp)</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($project->billings as $billing)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <a href="{{ route('billings.show', $billing) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                    {{ $billing->billing_no }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($billing->billing_date)->format('m/d/Y') }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ number_format($billing->amount, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @switch($billing->status)
                                                        @case('pending') bg-yellow-200 text-yellow-800 @break
                                                        @case('approved') bg-blue-200 text-blue-800 @break
                                                        @case('invoiced') bg-green-200 text-green-800 @break
                                                        @case('rejected') bg-red-200 text-red-800 @break
                                                    @endswitch">
                                                    {{ ucfirst($billing->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('billings.show', $billing) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-2 text-center text-gray-500">
                                                No billing requests created for this project yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <hr class="my-6">

                                    <div x-data="{ open: false }" class="mt-6 mb-6">
                    {{-- Make the header clickable --}}
                    <div class="flex justify-between items-center mb-2 cursor-pointer" @click="open = !open">
                        <h3 class="text-lg font-semibold">WBS Material Requirements & Stock</h3>
                        {{-- Add a visual indicator (+/- or arrow) --}}
                        <span class="text-indigo-600 font-medium text-xl" x-text="open ? '-' : '+'"></span>
                    </div>

                    {{-- The table container, now controlled by 'open' --}}
                    <div x-show="open" x-transition class="overflow-x-auto mt-2 border rounded">
                        @php
                            // Call the new method to get the summary data
                            $wbsMaterialSummary = $project->getMaterialStockSummary();
                        @endphp
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Budgeted</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Used</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">On Order</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">On Hand (Project)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($wbsMaterialSummary as $material)
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap font-mono">{{ $material['item_code'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $material['item_name'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap">{{ $material['uom'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right">{{ number_format($material['budgeted_qty'], 2, ',', '.') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-orange-600">{{ number_format($material['used_qty'], 2, ',', '.') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right text-blue-600">{{ number_format($material['on_order_qty'], 2, ',', '.') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-semibold {{ $material['on_hand_qty'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($material['on_hand_qty'], 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-2 text-center text-gray-500">
                                            No materials found in the project's WBS/RAB or no stock movements recorded.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- End of x-show div --}}
                </div>


                    <hr class="my-6">

                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-lg font-semibold">Work Breakdown Structure (WBS) / Plan</h3>
                        @if(!in_array($project->status, ['completed', 'closed']))
                            <a href="{{ route('progress.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                + Add Progress Update
                            </a>
                        @endif
                    </div>

                    
                    
                    <div class="grid grid-cols-12 gap-2 text-sm font-bold text-gray-600 uppercase px-2 py-1 border-b">
                        <div class="col-span-4">Description</div>             
                        <div class="col-span-1">Code</div>
                        <div class="col-span-1">UOM</div>
                        <div class="col-span-1 text-right">Qty</div>
                        <div class="col-span-1 text-right">Budget (Rp)</div>   
                        <div class="col-span-2 text-right">Actual Cost (Rp)</div>
                        <div class="col-span-1 text-right">Budget Left (Rp)</div>
                        <div class="col-span-1 text-center">Progress</div>
                    </div>

                    @foreach ($project->quotation->items as $item)
                        @include('projects.partials.item-row', ['item' => $item, 'level' => 0])
                    @endforeach
                    <div class="flex justify-end mt-6">
                        <div class="w-64">
                            <div class="flex justify-between font-bold text-lg border-t-2 pt-2">
                                <span>Total Budget:</span>
                                <span>Rp {{ number_format($project->total_budget, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>