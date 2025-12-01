<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumbs :items="[
            ['label' => 'Projects', 'url' => route('projects.index')],
            ['label' => $project->project_code, 'url' => '']
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->quotation->project_name }}
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
                                <a href="{{ route('projects.adendums.index', $project) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                    📝 Adendums
                                </a>

                                <a href="{{ route('reports.material_flow', $project) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                    📊 Material Flow
                                </a>

                                <a href="{{ route('reports.monthly_progress', $project) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                    📅 Monthly Progress
                                </a>
                                
                                @if($project->isReadyForReport())
                                    <a href="{{ route('reports.project_performance', $project) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                        📈 S-Curve
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-3 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed" title="Project must be In Progress and have a Schedule to view report">
                                        📈 S-Curve (Not Ready)
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

                    <hr class="my-8 border-gray-100">

                    {{-- TABS SECTION --}}
                    <div x-data="{ activeTab: 'wbs' }">
                        
                        {{-- Tab Navigation --}}
                        <div class="border-b border-gray-200 mb-6">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button @click="activeTab = 'wbs'" 
                                    :class="activeTab === 'wbs' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                    Work Plan (WBS)
                                </button>
                                <button @click="activeTab = 'materials'" 
                                    :class="activeTab === 'materials' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                    Material Requests
                                </button>
                                <button @click="activeTab = 'billings'" 
                                    :class="activeTab === 'billings' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                    Billings
                                </button>
                                <button @click="activeTab = 'stock'" 
                                    :class="activeTab === 'stock' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                    Stock Status
                                </button>
                            </nav>
                        </div>

                        {{-- TAB 1: WBS / Work Plan --}}
                        <div x-show="activeTab === 'wbs'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Work Breakdown Structure</h3>
                                @if($project->status === 'in_progress')
                                    <a href="{{ route('progress.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Add Progress Update
                                    </a>
                                @endif
                            </div>

                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                                <div class="grid grid-cols-12 gap-4 text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3 bg-gray-50 border-b border-gray-100">
                                    <div class="col-span-3">Description</div>             
                                    <div class="col-span-1">Code</div>
                                    <div class="col-span-1">UOM</div>
                                    <div class="col-span-1 text-right">Original Qty</div>
                                    <div class="col-span-1 text-right">Revised Qty</div>
                                    <div class="col-span-1 text-right">Original Budget</div>   
                                    <div class="col-span-1 text-right">Revised Budget</div>
                                    <div class="col-span-1 text-right">Actual Cost</div>
                                    <div class="col-span-1 text-right">Budget Left</div>
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

                        {{-- TAB 2: Material Requests --}}
                        <div x-show="activeTab === 'materials'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Material Requests</h3>
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

                        {{-- TAB 3: Billings --}}
                        <div x-show="activeTab === 'billings'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Billing Requests</h3>
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

                        {{-- TAB 4: Stock Status --}}
                        <div x-show="activeTab === 'stock'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Material Requirements & Stock</h3>
                            </div>

                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                                @php
                                    // Call the new method to get the summary data
                                    $wbsMaterialSummary = $project->getMaterialStockSummary();
                                @endphp
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UOM</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Budgeted</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Used</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">On Order</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">On Hand (Project)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse ($wbsMaterialSummary as $material)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-500">{{ $material['item_code'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $material['item_name'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $material['uom'] }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right">{{ number_format($material['budgeted_qty'], 2, ',', '.') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-orange-600">{{ number_format($material['used_qty'], 2, ',', '.') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-blue-600">{{ number_format($material['on_order_qty'], 2, ',', '.') }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right font-semibold {{ $material['on_hand_qty'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                                        {{ number_format($material['on_hand_qty'], 2, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                                        <div class="flex flex-col items-center justify-center">
                                                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                                            <p class="text-base font-medium text-gray-900">No materials found</p>
                                                            <p class="text-sm text-gray-500 mt-1">Check the WBS or stock transactions.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div> {{-- End of x-data --}}

                </div>
            </div>
        </div>
    </div>

    {{-- Premium Quick Drill-Down Modal --}}
    <div id="taskDrillDownModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background overlay --}}
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 -z-10" aria-hidden="true" onclick="closeTaskDrillDown()"></div>

            {{-- Center modal --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full relative z-10">
                {{-- Compact Header with Gradient --}}
                <div class="relative bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 overflow-hidden">
                    {{-- Subtle decorative shape --}}
                    <div class="absolute top-0 right-0 -mt-8 -mr-8 w-24 h-24 bg-white opacity-5 rounded-full"></div>
                    
                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white" id="modalTaskTitle">Task Details</h3>
                                <p class="text-indigo-100 text-xs" id="modalTaskCode">-</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeTaskDrillDown()" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white bg-opacity-10 hover:bg-opacity-20 text-white transition-all duration-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Loading State --}}
                <div id="modalLoading" class="p-12 text-center">
                    <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-3 text-sm text-gray-600">Loading task details...</p>
                </div>

                {{-- Modal Content --}}
                <div id="modalContent" class="hidden">
                    <div class="px-6 py-5 space-y-5">
                        {{-- Compact Progress Card --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-4 shadow-md">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-white text-opacity-90 text-xs font-medium">Current Progress</p>
                                        <p class="text-3xl font-bold text-white" id="modalTaskProgress">0%</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-white text-opacity-90 text-xs font-medium">Quantity</p>
                                    <p class="text-lg font-bold text-white" id="modalTaskQuantity">-</p>
                                </div>
                            </div>
                        </div>

                        {{-- Cost Breakdown Section --}}
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Cost Breakdown</h4>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Budgeted</th>
                                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actual</th>
                                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100" id="costBreakdownTable">
                                        {{-- Populated by JavaScript --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Recent Updates Section --}}
                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Recent Updates</h4>
                            </div>
                            <div class="p-5 space-y-3 max-h-96 overflow-y-auto" id="recentUpdatesList">
                                {{-- Populated by JavaScript --}}
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 sm:flex sm:flex-row-reverse">
                        <button type="button" onclick="closeTaskDrillDown()" class="w-full inline-flex justify-center items-center rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors sm:w-auto">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openTaskDrillDown(taskId) {
            const modal = document.getElementById('taskDrillDownModal');
            const loading = document.getElementById('modalLoading');
            const content = document.getElementById('modalContent');
            
            // Show modal and loading state
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            content.classList.add('hidden');
            
            const url = `/quotation-items/${taskId}/drill-down`;
            
            // Fetch data - SIMPLIFIED: No separate API, just regular route
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Helper function to format dates
                    const formatDate = (dateString) => {
                        if (!dateString) return '-';
                        const date = new Date(dateString);
                        return date.toLocaleDateString('id-ID', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric' 
                        });
                    };
                    
                    // Update header
                    document.getElementById('modalTaskTitle').textContent = data.task.description;
                    document.getElementById('modalTaskCode').textContent = `Code: ${data.task.code || '-'}`;
                    document.getElementById('modalTaskProgress').textContent = `${data.task.progress}%`;
                    document.getElementById('modalTaskQuantity').textContent = `${data.task.quantity} ${data.task.uom}`;
                    
                    // Update cost breakdown
                    const costTable = document.getElementById('costBreakdownTable');
                    const formatCurrency = (val) => {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
                    };
                    
                    const categories = [
                        { name: 'Materials', key: 'materials' },
                        { name: 'Labor', key: 'labor' },
                        { name: 'Equipment', key: 'equipment' },
                        { name: 'Total', key: 'total' }
                    ];
                    
                    costTable.innerHTML = categories.map(cat => {
                        const budgeted = data.budget[cat.key];
                        const actual = data.actual[cat.key];
                        const variance = data.variance[cat.key];
                        const isTotal = cat.key === 'total';
                        
                        return `
                            <tr class="${isTotal ? 'bg-indigo-50 font-bold border-t-2 border-indigo-200' : 'hover:bg-gray-50 transition-colors'}">
                                <td class="px-5 py-3 ${isTotal ? 'text-sm font-bold text-gray-900' : 'text-sm font-medium text-gray-700'}">${cat.name}</td>
                                <td class="px-5 py-3 text-right ${isTotal ? 'text-sm font-bold text-gray-900' : 'text-sm text-gray-600'}">${formatCurrency(budgeted)}</td>
                                <td class="px-5 py-3 text-right ${isTotal ? 'text-sm font-bold text-orange-600' : 'text-sm text-orange-600'}">${formatCurrency(actual)}</td>
                                <td class="px-5 py-3 text-right ${isTotal ? 'text-sm font-bold' : 'text-sm font-semibold'} ${variance >= 0 ? 'text-green-600' : 'text-red-600'}">
                                    ${formatCurrency(variance)}
                                </td>
                            </tr>
                        `;
                    }).join('');
                    
                    // Update recent updates
                    const updatesList = document.getElementById('recentUpdatesList');
                    if (data.recent_updates.length === 0) {
                        updatesList.innerHTML = `
                            <div class="text-center py-8">
                                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="mt-3 text-sm text-gray-500">No progress updates yet</p>
                            </div>
                        `;
                    } else {
                        updatesList.innerHTML = `
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Progress</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Materials</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    ${data.recent_updates.map(update => {
                                        const materialsCount = update.materials_used.length;
                                        const materialsSummary = materialsCount > 0 
                                            ? update.materials_used.map(m => `${m.item} (${m.quantity} ${m.uom})`).join(', ')
                                            : '-';
                                        
                                        return `
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">${formatDate(update.date)}</td>
                                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${
                                                        update.progress == 100 ? 'bg-green-100 text-green-800' : 
                                                        update.progress >= 50 ? 'bg-blue-100 text-blue-800' : 
                                                        'bg-gray-100 text-gray-800'
                                                    }">
                                                        ${update.progress}%
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">${update.user}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate" title="${update.notes || '-'}">
                                                    ${update.notes || '-'}
                                                </td>
                                                <td class="px-4 py-3 text-center text-sm">
                                                    ${materialsCount > 0 ? `
                                                        <button onclick="alert('${materialsSummary.replace(/'/g, "\\'")}')" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                            ${materialsCount} item${materialsCount > 1 ? 's' : ''}
                                                        </button>
                                                    ` : '<span class="text-gray-400">-</span>'}
                                                </td>
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        `;
                    }
                    
                    // Hide loading, show content
                    loading.classList.add('hidden');
                    content.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error fetching task details:', error);
                    loading.innerHTML = `
                        <div class="text-center">
                            <svg class="h-12 w-12 text-red-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-sm text-red-600">Failed to load task details</p>
                            <button onclick="closeTaskDrillDown()" class="mt-4 text-sm text-indigo-600 hover:text-indigo-800">Close</button>
                        </div>
                    `;
                });
        }
        
        function closeTaskDrillDown() {
            document.getElementById('taskDrillDownModal').classList.add('hidden');
        }
        
        // Close on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !document.getElementById('taskDrillDownModal').classList.contains('hidden')) {
                closeTaskDrillDown();
            }
        });
    </script>
</x-app-layout>