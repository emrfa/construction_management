<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Construction Manager') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600&display=swap" rel="stylesheet" />
        
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
        
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            /* Elegant Scrollbar Hiding */
            .sidebar-scroll::-webkit-scrollbar { display: none; }
            .sidebar-scroll { -ms-overflow-style: none; scrollbar-width: none; }
            
            /* Smooth fade transition for Alpine x-show */
            [x-cloak] { display: none !important; }
        </style>

        @stack('head-scripts')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-600">
        <div class="h-screen bg-slate-50 flex overflow-hidden">
            
            {{-- === SIDEBAR === --}}
            <aside class="w-72 bg-slate-950 text-slate-300 shadow-2xl border-r border-white/5 flex-shrink-0 flex flex-col h-full relative z-20 transition-all duration-300 ease-in-out" 
                   x-data="{ 
                       searchTerm: '', 
                       get lowerSearch() { return this.searchTerm.toLowerCase() },
                       openSections: JSON.parse(localStorage.getItem('openSections')) || {
                           main: true, 
                           sales: true, 
                           projects: true, 
                           procurement: true,
                           workLibrary: true, 
                           inventory: true, 
                           billing: true, 
                           reports: true, 
                           settings: true
                       }
                   }"
                   x-init="$watch('openSections', (value) => {
                       localStorage.setItem('openSections', JSON.stringify(value))
                   })">
                
                {{-- Brand Logo Area --}}
                <div class="h-20 flex items-center justify-center border-b border-white/5 bg-slate-950/50 backdrop-blur-sm flex-shrink-0 relative">
                    <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"></div>
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <div class="filter drop-shadow-[0_0_8px_rgba(99,102,241,0.3)] transition-all duration-500 group-hover:drop-shadow-[0_0_15px_rgba(99,102,241,0.5)]">
                            <x-application-logo class="block h-10 w-auto fill-current text-white" />
                        </div>
                    </a>
                </div>

                {{-- Search Area --}}
                <div class="px-5 py-6 flex-shrink-0">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-500 group-focus-within:text-indigo-400 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               placeholder="Search..." 
                               x-model.debounce.300ms="searchTerm"
                               class="block w-full pl-10 pr-3 py-2 bg-slate-900/50 border border-slate-800 rounded-lg text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 shadow-inner uppercase tracking-wider"
                        />
                    </div>
                </div>
                
                @php
                // Styles
                $baseClasses = 'group flex items-center w-full text-left px-4 py-2 text-sm rounded-r-lg transition-all duration-200 relative overflow-hidden mx-0 border-l-[3px] border-transparent';
                $activeClasses = 'bg-gradient-to-r from-indigo-500/10 to-transparent border-indigo-500 text-white font-medium shadow-[inset_1px_0_0_rgba(255,255,255,0.05)]';
                $inactiveClasses = 'text-slate-400 hover:text-slate-100 hover:bg-white/[0.02]';
                $deadLinkClasses = 'text-slate-600 cursor-not-allowed opacity-60 hover:text-slate-500';
                
                $sectionHeaderClass = "w-full flex items-center justify-between px-4 py-3 text-[10px] uppercase font-bold tracking-widest text-slate-500 hover:text-slate-300 transition-colors duration-200 mt-4 mb-1 select-none cursor-pointer";
                
                // Small indicator dot for sub-menus
                $dotClass = "w-1.5 h-1.5 rounded-full mr-3 transition-all duration-300";
                $activeDot = "bg-indigo-400 shadow-[0_0_5px_rgba(99,102,241,0.8)]";
                $inactiveDot = "bg-slate-700 group-hover:bg-slate-500";
                @endphp
                
                <div class="flex-1 overflow-y-auto px-0 space-y-1 sidebar-scroll pb-10">
                    <nav class="pr-3">
                        
                        {{-- Section: Dashboard --}}
                        <div x-show="!lowerSearch || 'dashboard'.includes(lowerSearch)">
                            <a href="{{ route('dashboard') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('dashboard') ? $activeClasses : $inactiveClasses }} mt-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-3 {{ request()->routeIs('dashboard') ? 'text-indigo-400' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span>Dashboard</span>
                            </a>
                        </div>

                        {{-- Section: Sales --}}
                        @canany(['manage clients', 'manage quotations'])
                        <div x-show="!lowerSearch || 'sales'.includes(lowerSearch) || '{{ strtolower(__('Clients')) }}'.includes(lowerSearch) || '{{ strtolower(__('Quotations')) }}'.includes(lowerSearch)">
                            <div @click="openSections.sales = !openSections.sales" class="{{ $sectionHeaderClass }}">
                                <span>Sales</span>
                                <svg class="h-2.5 w-2.5 opacity-50 transition-transform duration-300" :class="{ 'rotate-180': openSections.sales }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            
                            <div x-show="openSections.sales" x-collapse class="space-y-0.5">
                                @can('manage clients')
                                <a href="{{ route('clients.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('clients.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Clients')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('clients.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Clients') }}
                                </a>
                                @endcan
                                @can('manage quotations')
                                <a href="{{ route('quotations.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('quotations.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Quotations')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('quotations.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Quotations') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- Section: Projects --}}
                        @if(auth()->user()->can('view all projects') || auth()->user()->can('view own projects'))
                        <div x-show="!lowerSearch || 'projects'.includes(lowerSearch) || '{{ strtolower(__('Project List')) }}'.includes(lowerSearch) || 'project tasks'.includes(lowerSearch) || 'progress updates'.includes(lowerSearch)">
                            <div @click="openSections.projects = !openSections.projects" class="{{ $sectionHeaderClass }}">
                                <span>Projects</span>
                                <svg class="h-2.5 w-2.5 opacity-50 transition-transform duration-300" :class="{ 'rotate-180': openSections.projects }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="openSections.projects" x-collapse class="space-y-0.5">
                                <a href="{{ route('projects.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('projects.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Project List')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('projects.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Project List') }}
                                </a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'project tasks'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} bg-slate-800"></div>
                                    Project Tasks (WBS)
                                </a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'progress updates'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} bg-slate-800"></div>
                                    Progress Updates
                                </a>
                            </div>
                        </div>
                        @endif

                        {{-- Section: Procurement --}}
                        @canany(['manage suppliers', 'manage purchase_orders'])
                        <div x-show="!lowerSearch || 'procurement'.includes(lowerSearch) || '{{ strtolower(__('Suppliers')) }}'.includes(lowerSearch) || '{{ strtolower(__('Purchase Orders')) }}'.includes(lowerSearch)">
                            <div @click="openSections.procurement = !openSections.procurement" class="{{ $sectionHeaderClass }}">
                                <span>Procurement</span>
                                <svg class="h-2.5 w-2.5 opacity-50 transition-transform duration-300" :class="{ 'rotate-180': openSections.procurement }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="openSections.procurement" x-collapse class="space-y-0.5">
                                @can('manage suppliers')
                                <a href="{{ route('suppliers.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('suppliers.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Suppliers')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('suppliers.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Suppliers') }}
                                </a>
                                @endcan
                                @can('manage purchase_orders')
                                <a href="{{ route('purchase-orders.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('purchase-orders.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Purchase Orders')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('purchase-orders.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Purchase Orders') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- Section: Work Library --}}
                        @canany(['manage ahs_library', 'manage work_types', 'manage work_items'])
                        <div x-show="!lowerSearch || 'work library'.includes(lowerSearch) || '{{ strtolower(__('AHS Library')) }}'.includes(lowerSearch) || '{{ strtolower(__('Work Types')) }}'.includes(lowerSearch) || '{{ strtolower(__('Work Items')) }}'.includes(lowerSearch)">
                            <div @click="openSections.workLibrary = !openSections.workLibrary" class="{{ $sectionHeaderClass }}">
                                <span>Work Library</span>
                                <svg class="h-2.5 w-2.5 opacity-50 transition-transform duration-300" :class="{ 'rotate-180': openSections.workLibrary }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="openSections.workLibrary" x-collapse class="space-y-0.5">
                                @can('manage ahs_library')
                                <a href="{{ route('ahs-library.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('ahs-library.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('AHS Library')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('ahs-library.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('AHS Library') }}
                                </a>
                                @endcan
                                @can('manage work_types')
                                <a href="{{ route('work-types.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('work-types.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Work Types')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('work-types.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Work Types') }}
                                </a>
                                @endcan
                                @can('manage work_items')
                                <a href="{{ route('work-items.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('work-items.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Work Items')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('work-items.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Work Items') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                        @endcanany
                        
                        {{-- Section: Inventory (Restored ALL Items) --}}
                        @if(auth()->user()->can('manage inventory') || auth()->user()->can('view inventory'))
                        <div x-show="!lowerSearch || 'inventory'.includes(lowerSearch) || '{{ strtolower(__('Item Master')) }}'.includes(lowerSearch) || '{{ strtolower(__('Item Categories')) }}'.includes(lowerSearch) || '{{ strtolower(__('Equipment')) }}'.includes(lowerSearch) || '{{ strtolower(__('Labor Rates')) }}'.includes(lowerSearch) || '{{ strtolower(__('Stock Ledger')) }}'.includes(lowerSearch) || 'material usage'.includes(lowerSearch)">
                            <div @click="openSections.inventory = !openSections.inventory" class="{{ $sectionHeaderClass }}">
                                <span>Inventory</span>
                                <svg class="h-2.5 w-2.5 opacity-50 transition-transform duration-300" :class="{ 'rotate-180': openSections.inventory }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="openSections.inventory" x-collapse class="space-y-0.5">
                                @can('manage inventory')
                                <a href="{{ route('goods-receipts.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('goods-receipts.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Receiving')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('goods-receipts.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Receiving') }}
                                </a>
                                @endcan
                                
                                <a href="{{ route('inventory-items.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('inventory-items.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Item Master')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('inventory-items.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Item Master') }}
                                </a>
                                
                                @can('manage inventory')
                                <a href="{{ route('item-categories.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('item-categories.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Item Categories')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('item-categories.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Item Categories') }}
                                </a>
                                @endcan
                                
                                @can('manage equipment')
                                <a href="{{ route('equipment.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('equipment.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Equipment')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('equipment.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Equipment') }}
                                </a>
                                @endcan
                                
                                @can('manage labor_rates')
                                <a href="{{ route('labor-rates.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('labor-rates.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Labor Rates')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('labor-rates.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Labor Rates') }}
                                </a>
                                @endcan

                                <a href="{{ route('stock-ledger.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('stock-ledger.index') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Stock Ledger')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('stock-ledger.index') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Stock Ledger') }}
                                </a>
                                
                                <a href="{{ route('reports.stock_balance') }}" class="{{ $baseClasses }} {{ request()->routeIs('reports.stock_balance') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Stock Balance')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('reports.stock_balance') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Stock Balance') }}
                                </a>
                                
                                <a href="{{ route('stock-locations.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('stock-locations.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Stock Locations')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('stock-locations.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Stock Locations') }}
                                </a>
                                
                                <a href="{{ route('stock-adjustments.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('stock-adjustments.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Stock Adjustments')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('stock-adjustments.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Stock Adjustments') }}
                                </a>
                                
                                <a href="{{ route('stock-overview.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('stock-overview.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Stock Overview')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('stock-overview.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Stock Overview') }}
                                </a>
                                
                                <a href="{{ route('material-usage.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('material-usage.index') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || 'material usage'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('material-usage.index') ? $activeDot : $inactiveDot }}"></div>
                                    Material Usage
                                </a>
                            </div>
                        </div>
                        @endif

                         {{-- Section: Billing --}}
                        @canany(['manage billings', 'manage invoices', 'manage payments'])
                        <div x-show="!lowerSearch || 'billing'.includes(lowerSearch) || '{{ strtolower(__('Billings')) }}'.includes(lowerSearch) || '{{ strtolower(__('Invoices')) }}'.includes(lowerSearch) || 'payments'.includes(lowerSearch)">
                             <div @click="openSections.billing = !openSections.billing" class="{{ $sectionHeaderClass }}">
                                <span>Billing</span>
                                <svg class="h-2.5 w-2.5 opacity-50 transition-transform duration-300" :class="{ 'rotate-180': openSections.billing }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="openSections.billing" x-collapse class="space-y-0.5">
                                @can('manage billings')
                                <a href="{{ route('billings.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('billings.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Billings')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('billings.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Billings') }}
                                </a>
                                @endcan
                                @can('manage invoices')
                                <a href="{{ route('invoices.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('invoices.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || '{{ strtolower(__('Invoices')) }}'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('invoices.*') ? $activeDot : $inactiveDot }}"></div>
                                    {{ __('Invoices') }}
                                </a>
                                @endcan
                                @can('manage payments')
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'payments'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} bg-slate-800"></div>
                                    Payments
                                </a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- Section: Reports (Restored) --}}
                        @role('Admin')
                        <div x-show="!lowerSearch || 'reports'.includes(lowerSearch) || 'project costing'.includes(lowerSearch) || 'inventory levels'.includes(lowerSearch)">
                            <div @click="openSections.reports = !openSections.reports" class="{{ $sectionHeaderClass }}">
                                <span>Reports</span>
                                <svg class="h-2.5 w-2.5 opacity-50 transition-transform duration-300" :class="{ 'rotate-180': openSections.reports }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="openSections.reports" x-collapse class="space-y-0.5">
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'project costing'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} bg-slate-800"></div>
                                    Project Costing
                                </a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'inventory levels'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} bg-slate-800"></div>
                                    Inventory Levels
                                </a>
                            </div>
                        </div>
                        @endrole

                        {{-- Section: Settings --}}
                        @role('Admin')
                        <div x-show="!lowerSearch || 'settings'.includes(lowerSearch) || 'users & roles'.includes(lowerSearch) || 'company details'.includes(lowerSearch)">
                            <div @click="openSections.settings = !openSections.settings" class="{{ $sectionHeaderClass }}">
                                <span>Settings</span>
                                <svg class="h-2.5 w-2.5 opacity-50 transition-transform duration-300" :class="{ 'rotate-180': openSections.settings }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <div x-show="openSections.settings" x-collapse class="space-y-0.5">
                                <a href="{{ route('users.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('users.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || 'users & roles'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('users.*') ? $activeDot : $inactiveDot }}"></div>
                                    Users & Roles
                                </a>
                                <a href="{{ route('roles.index') }}" class="{{ $baseClasses }} {{ request()->routeIs('roles.*') ? $activeClasses : $inactiveClasses }}" x-show="!lowerSearch || 'manage roles'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} {{ request()->routeIs('roles.*') ? $activeDot : $inactiveDot }}"></div>
                                    Manage Roles
                                </a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'company details'.includes(lowerSearch)">
                                    <div class="{{ $dotClass }} bg-slate-800"></div>
                                    Company Details
                                </a>
                            </div>
                        </div>
                        @endrole
                    </nav>
                </div>

                {{-- User Profile / Footer --}}
                <div class="flex-shrink-0 border-t border-white/5 bg-slate-950 p-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center w-full group focus:outline-none">
                            <div class="relative">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-lg group-hover:shadow-indigo-500/50 transition-shadow duration-300">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <div class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-emerald-500 border-2 border-slate-900"></div>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-medium text-slate-200 group-hover:text-white transition-colors">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 group-hover:text-slate-400 transition-colors truncate w-32">{{ Auth::user()->email }}</p>
                            </div>
                            <svg class="ml-auto h-4 w-4 text-slate-500 group-hover:text-slate-300 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                            </svg>
                        </button>
                        
                        {{-- Dropdown Menu --}}
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                             x-transition:leave-leave="transform opacity-0 scale-95 translate-y-2"
                             class="absolute bottom-full left-0 w-full mb-2 bg-slate-900 border border-slate-800 rounded-lg shadow-2xl overflow-hidden z-50" style="display: none;">
                            
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-indigo-600 hover:text-white transition-colors">
                                Profile Settings
                            </a>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-red-600 hover:text-white transition-colors"
                                   onclick="event.preventDefault(); this.closest('form').submit();">
                                    Sign Out
                                </a>
                            </form>
                        </div>
                    </div>
                </div> 
            </aside>
            
            {{-- === MAIN CONTENT === --}}
            <div class="flex-1 overflow-y-auto relative h-full">
                
                @if (isset($header))
                    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-10">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <div class="p-6 lg:p-8 bg-slate-50 min-h-full">
                    {{ $slot }}
                </div>
            </div>
        </div>
        
        @stack('scripts')
    </body>
</html>