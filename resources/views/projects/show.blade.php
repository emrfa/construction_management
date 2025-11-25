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

                            {{-- NEW DASHBOARD LAYOUT --}}
                            
                            {{-- 1. Top Row: Financial Stat Cards --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                {{-- Total Budget --}}
                                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                                    <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Budget</dt>
                                    <dd class="mt-2 text-3xl font-bold text-gray-900">
                                        <span class="text-lg text-gray-400 align-top">Rp</span>{{ number_format($project->total_budget, 0, ',', '.') }}
                                    </dd>
                                    <div class="absolute bottom-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                        <svg class="w-16 h-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                </div>

                                {{-- Actual Cost --}}
                                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                                    <div class="absolute right-0 top-0 h-full w-1 bg-orange-500"></div>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Actual Cost</dt>
                                    <dd class="mt-2 text-3xl font-bold text-orange-600">
                                        <span class="text-lg text-orange-400 align-top">Rp</span>{{ number_format($project->actual_cost, 0, ',', '.') }}
                                    </dd>
                                    <div class="absolute bottom-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                        <svg class="w-16 h-16 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                    </div>
                                </div>

                                {{-- Remaining Budget --}}
                                <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-shadow">
                                    <div class="absolute right-0 top-0 h-full w-1 {{ $project->budget_left >= 0 ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Remaining Budget</dt>
                                    <dd class="mt-2 text-3xl font-bold {{ $project->budget_left >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        <span class="text-lg opacity-60 align-top">Rp</span>{{ number_format($project->budget_left, 0, ',', '.') }}
                                    </dd>
                                    <div class="absolute bottom-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                        <svg class="w-16 h-16 {{ $project->budget_left >= 0 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Second Row: Details & Timeline --}}
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                                {{-- Project Details Card --}}
                                <div class="lg:col-span-1 bg-white rounded-xl border border-gray-100 shadow-sm p-6 h-full">
                                    <div class="flex items-center gap-2 mb-4 border-b border-gray-50 pb-2">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Project Details</h4>
                                    </div>
                                    
                                    <div class="space-y-5">
                                        <div>
                                            <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Client</span>
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                                    {{ substr($project->client->name, 0, 2) }}
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900">{{ $project->client->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $project->client->company_name }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <span class="block text-xs font-medium text-gray-500 uppercase mb-1">Location</span>
                                            @if(empty($project->location) && $project->status === 'initiated')
                                                <div class="relative rounded-md shadow-sm">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    </div>
                                                    <input type="text" name="location" 
                                                        value="{{ old('location') }}" 
                                                        class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                                                        placeholder="Enter project location">
                                                </div>
                                            @else
                                                <div class="flex items-start gap-2 text-gray-700">
                                                    <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    <span class="font-medium">{{ $project->location ?? 'Not specified' }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Timeline Card --}}
                                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6 h-full">
                                    <div class="flex justify-between items-center mb-4 border-b border-gray-50 pb-2">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Project Timeline</h4>
                                        </div>
                                        @if ($project->status === 'initiated')
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-medium hover:bg-indigo-100 transition">
                                                Save Dates
                                            </button>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        {{-- Start Date --}}
                                        <div class="relative">
                                            <label for="start_date" class="block text-xs font-medium text-gray-500 uppercase mb-1">Start Date</label>
                                            @if ($project->status === 'initiated')
                                                <input type="date" name="start_date" id="start_date" 
                                                    value="{{ old('start_date', $project->start_date) }}"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            @else
                                                <div class="flex items-center gap-3">
                                                    <div class="bg-blue-50 p-2 rounded-lg text-blue-600">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-lg font-bold text-gray-900">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '-' }}</p>
                                                        <p class="text-xs text-gray-500">Planned Start</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- End Date --}}
                                        <div class="relative">
                                            <label for="end_date" class="block text-xs font-medium text-gray-500 uppercase mb-1">End Date</label>
                                            @if ($project->status === 'initiated')
                                                <input type="date" name="end_date" id="end_date" 
                                                    value="{{ old('end_date', $project->end_date) }}"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            @else
                                                <div class="flex items-center gap-3">
                                                    <div class="bg-purple-50 p-2 rounded-lg text-purple-600">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    </div>
                                                    <div>
                                                        <p class="text-lg font-bold text-gray-900">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d M Y') : '-' }}</p>
                                                        <p class="text-xs text-gray-500">Planned Completion</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($project->actual_end_date)
                                        <div class="mt-6 pt-4 border-t border-gray-50">
                                            <div class="flex items-center gap-3">
                                                <div class="bg-green-50 p-2 rounded-lg text-green-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </div>
                                                <div>
                                                    <p class="text-lg font-bold text-green-600">{{ \Carbon\Carbon::parse($project->actual_end_date)->format('d M Y') }}</p>
                                                    <p class="text-xs text-gray-500">Actual Completion Date</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-6">
                                        <a href="{{ route('projects.scheduler', $project) }}" class="inline-flex items-center justify-center w-full px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            Open Gantt Scheduler
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>

                    <hr class="my-6">

                    <div class="mt-6 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold">Material Requests</h3>
                            {{-- Link to the create form, passing the project ID --}}
                            {{-- Link to the create form, passing the project ID --}}
                            @if($project->status === 'in_progress')
                                <a href="{{ route('material-requests.create', ['project_id' => $project->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Request Materials
                                </a>
                            @endif
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Req #</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required Date</th> 
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($project->materialRequests as $request)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('material-requests.show', $request) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                    {{ $request->request_code }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ \Carbon\Carbon::parse($request->request_date)->format('d M Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $request->requester->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $request->required_date ? \Carbon\Carbon::parse($request->required_date)->format('d M Y') : '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    @switch($request->status)
                                                        @case('draft') bg-gray-100 text-gray-800 @break
                                                        @case('pending_approval') bg-yellow-100 text-yellow-800 @break
                                                        @case('approved') bg-green-100 text-green-800 @break
                                                        @case('rejected') bg-red-100 text-red-800 @break
                                                        @case('partially_fulfilled') bg-blue-100 text-blue-800 @break
                                                        @case('fulfilled') bg-purple-100 text-purple-800 @break
                                                        @case('cancelled') bg-gray-100 text-gray-800 @break
                                                    @endswitch">
                                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('material-requests.show', $request) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                                <div class="flex flex-col items-center justify-center">
                                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                                    <p class="text-base font-medium text-gray-900">No material requests yet</p>
                                                    <p class="text-sm text-gray-500 mt-1">Requests created for this project will appear here.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <hr class="my-6">

                    <div class="mt-6 mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-lg font-semibold">Billing Requests</h3>
                            @if($project->status === 'in_progress')
                                <a href="{{ route('billings.create', ['project_id' => $project->id]) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Create Billing
                                </a>
                            @endif
                        </div>

                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing #</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (Rp)</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($project->billings as $billing)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <a href="{{ route('billings.show', $billing) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                        {{ $billing->billing_no }}
                                                    </a>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ \Carbon\Carbon::parse($billing->billing_date)->format('d M Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ number_format($billing->amount, 0, ',', '.') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full
                                                        @switch($billing->status)
                                                            @case('pending') bg-yellow-100 text-yellow-800 @break
                                                            @case('approved') bg-blue-100 text-blue-800 @break
                                                            @case('invoiced') bg-green-100 text-green-800 @break
                                                            @case('rejected') bg-red-100 text-red-800 @break
                                                        @endswitch">
                                                        {{ ucfirst($billing->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="{{ route('billings.show', $billing) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                        <p class="text-base font-medium text-gray-900">No billing requests yet</p>
                                                        <p class="text-sm text-gray-500 mt-1">Billings created for this project will appear here.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
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
                        @if($project->status === 'in_progress')
                            <a href="{{ route('progress.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add Progress Update
                            </a>
                        @endif
                    </div>

                    
                    
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="grid grid-cols-12 gap-4 text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3 bg-gray-50 border-b border-gray-100">
                            <div class="col-span-4">Description</div>             
                            <div class="col-span-1">Code</div>
                            <div class="col-span-1">UOM</div>
                            <div class="col-span-1 text-right">Qty</div>
                            <div class="col-span-1 text-right">Budget (Rp)</div>   
                            <div class="col-span-2 text-right">Actual Cost (Rp)</div>
                            <div class="col-span-1 text-right">Budget Left (Rp)</div>
                            <div class="col-span-1 text-center">Progress</div>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach ($project->quotation->items as $item)
                                @include('projects.partials.item-row', ['item' => $item, 'level' => 0])
                            @endforeach
                        </div>
                    </div>
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