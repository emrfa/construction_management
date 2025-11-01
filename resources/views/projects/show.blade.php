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

                    

                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Project Summary</h3>
                            <div class="flex items-center space-x-1">
                           @if ($project->status === 'initiated')
                                <x-primary-button onclick="document.getElementById('project-update-form').submit();">
                                    {{ __('Save Changes') }}
                                </x-primary-button>
                            @endif

                            <a href="{{ route('reports.material_flow', $project) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                📊 Material Flow Report
                            </a>
                            <a href="{{ route('reports.project_performance', $project) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                📈 Performance Report
                            </a>
                            @if ($project->status == 'in_progress')
                                <form method="POST" action="{{ route('projects.complete', $project) }}" onsubmit="return confirm('Are you sure you want to mark this project as completed?');" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Mark as Complete
                                    </button>
                                </form>
                            @elseif ($project->status == 'completed')
                                <form method="POST" action="{{ route('projects.close', $project) }}" onsubmit="return confirm('Are you sure you want to close this project? This action might restrict further changes.');" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Mark as Closed
                                    </button>
                                </form>
                            @elseif ($project->status == 'closed')
                                <span class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-600 uppercase tracking-widest">
                                    Project Closed
                                </span>
                            @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('projects.update', $project) }}" id="project-update-form">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <x-input-label :value="__('Client')" />
                                <p class="text-lg font-medium">{{ $project->client->name }}</p>
                                <p class="text-sm">{{ $project->client->company_name }}</p>
                            </div>

                            <div>
                                <x-input-label :value="__('Project Name')" />
                                <p class="text-lg font-medium">{{ $project->quotation->project_name }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 border-t pt-4">
                                <div>
                                    <x-input-label :value="__('Total Budget (Rp)')" />
                                    <p class="text-lg font-semibold">{{ number_format($project->total_budget, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <x-input-label :value="__('Actual Cost (Rp)')" />
                                    <p class="text-lg font-semibold text-orange-600">{{ number_format($project->actual_cost, 0, ',', '.') }}</p> {{-- Use accessor --}}
                                </div>
                                <div>
                                    <x-input-label :value="__('Budget Left (Rp)')" />
                                    <p class="text-lg font-semibold {{ $project->budget_left >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($project->budget_left, 0, ',', '.') }} {{-- Use accessor --}}
                                    </p>
                                </div>
                            </div>
                           <div>
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                @if ($project->status === 'initiated')
                                
                                    <x-text-input
                                        id="start_date"
                                        class="block mt-1 w-full"
                                        type="date"
                                        name="start_date"
                                        :value="old('start_date', $project->start_date)"
                                    />
                                @else
                             
                                    <p class="mt-1 block w-full px-3 py-2 text-gray-700">
                                        {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : 'Not set' }}
                                    </p>
                                    {{-- Optional: Add hidden input if value needs to be submitted even when read-only --}}
                                    {{-- <input type="hidden" name="start_date" value="{{ $project->start_date }}"> --}}
                                @endif

                                <x-input-label for="end_date" :value="__('End Date')" class="mt-2" />
                                @if ($project->status === 'initiated')
            
                                    <x-text-input
                                        id="end_date"
                                        class="block mt-1 w-full"
                                        type="date"
                                        name="end_date"
                                        :value="old('end_date', $project->end_date)"
                                    />
                                @else
        
                                    <p class="mt-1 block w-full px-3 py-2 text-gray-700">
                                        {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : 'Not set' }}
                                    </p>
                                    {{-- Optional: Add hidden input if value needs to be submitted even when read-only --}}
                                    {{-- <input type="hidden" name="end_date" value="{{ $project->end_date }}"> --}}
                                @endif

                                @if($project->actual_end_date)
                                <x-input-label :value="__('Actual End Date')" class="mt-2 font-semibold text-green-700"/>
                                <p class="mt-1 block w-full px-3 py-2 font-semibold text-green-700">
                                    {{ \Carbon\Carbon::parse($project->actual_end_date)->format('Y-m-d') }}
                                </p>
                                @endif

                            </div>
                            <div>
                                <x-input-label :value="__('Status')" />
                                <p class="mt-1">
                                    <span class="px-2 inline-flex text-sm leading-5 font-semibold rounded-full
                                        @switch($project->status)
                                            @case('initiated') bg-blue-200 text-blue-800 @break
                                            @case('in_progress') bg-yellow-200 text-yellow-800 @break
                                            @case('completed') bg-green-200 text-green-800 @break
                                            @case('closed') bg-gray-200 text-gray-800 @break
                                        @endswitch">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('projects.scheduler', $project) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            Edit Schedule
                        </a>
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
                            $wbsMaterialSummary = $project->getWbsMaterialSummary();
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