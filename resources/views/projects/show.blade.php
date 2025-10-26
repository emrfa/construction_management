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

                    <form method="POST" action="{{ route('projects.update', $project) }}">
                        @csrf
                        @method('PUT')

                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Project Summary</h3>
                            <x-primary-button>
                                {{ __('Save Changes') }}
                            </x-primary-button>
                        </div>

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

                            <div>
                                <x-input-label :value="__('Total Budget (Rp)')" />
                                <p class="text-lg font-semibold">{{ number_format($project->total_budget, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $project->start_date)" />

                                <x-input-label for="end_date" :value="__('End Date')" class="mt-2" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $project->end_date)" />
                            </div>
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="initiated" {{ $project->status == 'initiated' ? 'selected' : '' }}>Initiated</option>
                                    <option value="in_progress" {{ $project->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $project->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="closed" {{ $project->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                        </div>
                    </form>

                    <hr class="my-6">

                    <div class="mt-6 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold">Material Requests</h3>
                            {{-- Link to the create form, passing the project ID --}}
                            <a href="{{ route('material-requests.create', ['project_id' => $project->id]) }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm">
                                + Request Materials
                            </a>
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
                            <a href="{{ route('billings.create', ['project_id' => $project->id]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                + Create Billing
                            </a>
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
                        <div class="flex justify-between items-center mb-2 cursor-pointer" @click="open = !open">
                            <h3 class="text-lg font-semibold">Project Material Stock</h3>
                            <span x-text="open ? '– Hide' : '+ Show'" class="text-indigo-600 font-medium"></span>
                        </div>

                        <div x-show="open" x-transition class="overflow-x-auto mt-2">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty on Hand</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($stockSummary as $row)
                                        <tr>
                                            <td class="px-4 py-2">{{ $row['item_code'] }}</td>
                                            <td class="px-4 py-2">{{ $row['item_name'] }}</td>
                                            <td class="px-4 py-2">{{ $row['uom'] }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($row['quantity'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-2 text-center text-gray-500">
                                                No materials received for this project yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>


                    <hr class="my-6">

                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-lg font-semibold">Work Breakdown Structure (WBS) / Plan</h3>
                        <a href="{{ route('progress.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                            + Add Progress Update
                        </a>
                    </div>

                    
                    
                    <div class="grid grid-cols-12 gap-2 text-sm font-bold text-gray-600 uppercase px-2 py-1 border-b">
                        <div class="col-span-5">Description</div>
                        <div class="col-span-1">Code</div>
                        <div class="col-span-1">UOM</div>
                        <div class="col-span-1 text-right">Qty</div>
                        <div class="col-span-2 text-right">Unit Price</div>
                        <div class="col-span-1 text-right">Subtotal</div>
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