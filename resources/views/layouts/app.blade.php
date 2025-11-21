<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-t">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Construction Manager') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
        
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            /* Hide scrollbar for Chrome, Safari and Opera */
            .sidebar-scroll::-webkit-scrollbar {
                display: none;
            }
            /* Hide scrollbar for IE, Edge and Firefox */
            .sidebar-scroll {
                -ms-overflow-style: none;  /* IE and Edge */
                scrollbar-width: none;  /* Firefox */
            }
        </style>

        @stack('head-scripts')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="h-screen bg-gray-100 flex">
            {{-- === SIDEBAR === --}}
            <aside class="w-64 bg-gradient-to-b from-[#1D2B41] to-[#0F172A] shadow-lg border-r border-slate-700/60 p-4 flex-shrink-0 flex flex-col h-full" 
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
                
                <div class="pb-4 mb-4 border-b border-slate-700/60 flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center">
                        <x-application-logo class="block h-16 w-auto fill-current text-white" />
                    </a>
                </div>

                <div class="pb-4 mb-4 border-b border-slate-700/60 flex-shrink-0">
                    <form onsubmit="return false;">
                        <x-input-label for="sidebar-search" class="sr-only">Search</x-input-label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none" aria-hidden="true">
                                <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <x-text-input id="sidebar-search" 
                                          class="block mt-1 w-full pl-10 bg-white/5 text-gray-200 border-gray-700/60 placeholder-gray-500 focus:border-gray-500 focus:ring-gray-500 focus:ring-opacity-50" 
                                          type="text" 
                                          name="search" 
                                          placeholder="Search..." 
                                          x-model.debounce.300ms="searchTerm" 
                            />
                        </div>
                    </form>
                </div>
                
                @php
                $baseClasses = 'flex items-center w-full text-left px-2.5 py-1.5 text-sm rounded-md transition-colors duration-150';
                $activeClasses = 'bg-white/10 text-white font-medium';
                $inactiveClasses = 'text-gray-400 hover:bg-white/5 hover:text-white';
                $deadLinkClasses = 'text-gray-700 cursor-not-allowed';
                @endphp
                
                <div class="flex-1 overflow-y-auto space-y-6 sidebar-scroll">
                    <nav>
                        {{-- Section: Main --}}
                        <div x-show="!lowerSearch || 'dashboard'.includes(lowerSearch)">
                            <a href="{{ route('dashboard') }}"
                               class="w-full flex items-center text-xs uppercase font-semibold tracking-wider px-2.5 mb-1 transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-500 hover:text-white' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Dashboard
                            </a>
                        </div>


                        {{-- Section: Sales --}}
                        @canany(['manage clients', 'manage quotations'])
                        <div x-show="!lowerSearch || 'Sales'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Clients')) }}'.includes(lowerSearch) || '{{ strtolower(__('Quotations')) }}'.includes(lowerSearch)">
                            <button type="button" @click="openSections.sales = !openSections.sales" class="w-full flex items-center justify-between px-2.5 text-xs uppercase font-semibold tracking-wider text-gray-500 mb-1 hover:text-white transition-colors duration-150 {{ request()->routeIs('clients.*') || request()->routeIs('quotations.*') ? 'text-white' : '' }}">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    <span>Sales</span>
                                </span>
                                <svg class="h-3 w-3 text-gray-500 transform transition-transform" :class="{ 'rotate-180': !openSections.sales }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="mt-1 space-y-1" x-show="openSections.sales" x-transition>
                                @can('manage clients')
                                <a href="{{ route('clients.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('clients.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Clients')) }}'.includes(lowerSearch)">
                                    {{ __('Clients') }}
                                </a>
                                @endcan
                                @can('manage quotations')
                                <a href="{{ route('quotations.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('quotations.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Quotations')) }}'.includes(lowerSearch)">
                                    {{ __('Quotations') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- Section: Projects --}}
                        @if(auth()->user()->can('view all projects') || auth()->user()->can('view own projects'))
                        <div x-show="!lowerSearch || 'Projects'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Project List')) }}'.includes(lowerSearch) || 'project tasks (wbs)'.includes(lowerSearch) || 'progress updates'.includes(lowerSearch)">
                            <button type="button" @click="openSections.projects = !openSections.projects" class="w-full flex items-center justify-between px-2.5 text-xs uppercase font-semibold tracking-wider text-gray-500 mb-1 hover:text-white transition-colors duration-150 {{ request()->routeIs('projects.*') ? 'text-white' : '' }}">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                                    <span>Projects</span>
                                </span>
                                <svg class="h-3 w-3 text-gray-500 transform transition-transform" :class="{ 'rotate-180': !openSections.projects }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="mt-1 space-y-1" x-show="openSections.projects" x-transition>
                                <a href="{{ route('projects.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('projects.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Project List')) }}'.includes(lowerSearch)">
                                    {{ __('Project List') }}
                                </a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'project tasks (wbs)'.includes(lowerSearch)">Project Tasks (WBS)</a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'progress updates'.includes(lowerSearch)">Progress Updates</a>
                            </div>
                        </div>
                        @endif

                        {{-- Section: Procurement --}}
                        @canany(['manage suppliers', 'manage purchase_orders', 'create material_request'])
                        <div x-show="!lowerSearch || 'Procurement'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Suppliers')) }}'.includes(lowerSearch) || '{{ strtolower(__('Purchase Orders')) }}'.includes(lowerSearch)">
                            <button type="button" @click="openSections.procurement = !openSections.procurement" class="w-full flex items-center justify-between px-2.5 text-xs uppercase font-semibold tracking-wider text-gray-500 mb-1 hover:text-white transition-colors duration-150 {{ request()->routeIs('suppliers.*') || request()->routeIs('purchase-orders.*') ? 'text-white' : '' }}">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    <span>Procurement</span>
                                </span>
                                <svg class="h-3 w-3 text-gray-500 transform transition-transform" :class="{ 'rotate-180': !openSections.procurement }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="mt-1 space-y-1" x-show="openSections.procurement" x-transition>
                                @can('manage suppliers')
                                <a href="{{ route('suppliers.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('suppliers.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Suppliers')) }}'.includes(lowerSearch)">
                                    {{ __('Suppliers') }}
                                </a>
                                @endcan
                                @can('manage purchase_orders')
                                <a href="{{ route('purchase-orders.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('purchase-orders.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Purchase Orders')) }}'.includes(lowerSearch)">
                                    {{ __('Purchase Orders') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- Section: Work Library --}}
                        @canany(['manage ahs_library', 'manage work_types', 'manage work_items'])
                        <div x-show="!lowerSearch || 'Work Library'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('AHS Library')) }}'.includes(lowerSearch) || '{{ strtolower(__('Work Types')) }}'.includes(lowerSearch) || '{{ strtolower(__('Work Items')) }}'.includes(lowerSearch)">
                            <button type="button" @click="openSections.workLibrary = !openSections.workLibrary" class="w-full flex items-center justify-between px-2.5 text-xs uppercase font-semibold tracking-wider text-gray-500 mb-1 hover:text-white transition-colors duration-150 {{ request()->routeIs('ahs-library.*') || request()->routeIs('work-types.*') || request()->routeIs('work-items.*') ? 'text-white' : '' }}">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                    <span>Work Library</span>
                                </span>
                                <svg class="h-3 w-3 text-gray-500 transform transition-transform" :class="{ 'rotate-180': !openSections.workLibrary }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="mt-1 space-y-1" x-show="openSections.workLibrary" x-transition>
                                @can('manage ahs_library')
                                <a href="{{ route('ahs-library.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('ahs-library.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('AHS Library')) }}'.includes(lowerSearch)">
                                    {{ __('AHS Library') }}
                                </a>
                                @endcan
                                @can('manage work_types')
                                <a href="{{ route('work-types.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('work-types.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Work Types')) }}'.includes(lowerSearch)">
                                    {{ __('Work Types') }}
                                </a>
                                @endcan
                                @can('manage work_items')
                                <a href="{{ route('work-items.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('work-items.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Work Items')) }}'.includes(lowerSearch)">
                                    {{ __('Work Items') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                        @endcanany
                        
                        {{-- Section: Inventory --}}
                        @if(auth()->user()->can('manage inventory') || auth()->user()->can('view inventory'))
                        <div x-show="!lowerSearch || 'Inventory'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Item Master')) }}'.includes(lowerSearch) || '{{ strtolower(__('Item Categories')) }}'.includes(lowerSearch) || '{{ strtolower(__('Equipment')) }}'.includes(lowerSearch) || '{{ strtolower(__('Labor Rates')) }}'.includes(lowerSearch) || '{{ strtolower(__('Stock Ledger')) }}'.includes(lowerSearch) || 'material usage'.includes(lowerSearch)">
                            <button type="button" @click="openSections.inventory = !openSections.inventory" class="w-full flex items-center justify-between px-2.5 text-xs uppercase font-semibold tracking-wider text-gray-500 mb-1 hover:text-white transition-colors duration-150 {{ request()->routeIs('inventory-items.*') || request()->routeIs('item-categories.*') || request()->routeIs('equipment.*') || request()->routeIs('labor-rates.*') || request()->routeIs('stock-ledger.*') || request()->routeIs('goods-receipts.*') || request()->routeIs('stock-locations.*') || request()->routeIs('stock-adjustments.*') || request()->routeIs('stock-overview.*') ? 'text-white' : '' }}">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    <span>Inventory</span>
                                </span>
                                <svg class="h-3 w-3 text-gray-500 transform transition-transform" :class="{ 'rotate-180': !openSections.inventory }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="mt-1 space-y-1" x-show="openSections.inventory" x-transition>
                                @can('manage inventory')
                                <a href="{{ route('goods-receipts.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('goods-receipts.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Receiving')) }}'.includes(lowerSearch)">
                                    {{ __('Receiving') }}
                                </a>
                                @endcan
                                <a href="{{ route('inventory-items.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('inventory-items.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Item Master')) }}'.includes(lowerSearch)">
                                    {{ __('Item Master') }}
                                </a>
                                @can('manage inventory')
                                <a href="{{ route('item-categories.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('item-categories.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Item Categories')) }}'.includes(lowerSearch)">
                                    {{ __('Item Categories') }}
                                </a>
                                @endcan
                                @can('manage equipment')
                                <a href="{{ route('equipment.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('equipment.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Equipment')) }}'.includes(lowerSearch)">
                                    {{ __('Equipment') }}
                                </a>
                                @endcan
                                @can('manage labor_rates')
                                <a href="{{ route('labor-rates.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('labor-rates.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Labor Rates')) }}'.includes(lowerSearch)">
                                    {{ __('Labor Rates') }}
                                </a>
                                @endcan
                                <a href="{{ route('stock-ledger.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('stock-ledger.index') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Stock Ledger')) }}'.includes(lowerSearch)">
                                    {{ __('Stock Ledger') }}
                                </a>
                                <a href="{{ route('reports.stock_balance') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('reports.stock_balance') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Stock Balance')) }}'.includes(lowerSearch)">
                                    {{ __('Stock Balance') }}
                                </a>
                                <a href="{{ route('stock-locations.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('stock-locations.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Stock Locations')) }}'.includes(lowerSearch)">
                                    {{ __('Stock Locations') }}
                                </a>
                                <a href="{{ route('stock-adjustments.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('stock-adjustments.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Stock Adjustments')) }}'.includes(lowerSearch)">
                                    {{ __('Stock Adjustments') }}
                                </a>
                                <a href="{{ route('stock-overview.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('stock-overview.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Stock Overview')) }}'.includes(lowerSearch)">
                                    {{ __('Stock Overview') }}
                                </a>
                                <a href="{{ route('material-usage.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('material-usage.index') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || 'material usage'.includes(lowerSearch)">
                                    Material Usage
                                </a>
                            </div>
                        </div>
                        @endif

                        {{-- Section: Billing --}}
                        @canany(['manage billings', 'manage invoices', 'manage payments'])
                        <div x-show="!lowerSearch || 'Billing'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Billings')) }}'.includes(lowerSearch) || '{{ strtolower(__('Invoices')) }}'.includes(lowerSearch) || 'payments'.includes(lowerSearch)">
                            <button type="button" @click="openSections.billing = !openSections.billing" class="w-full flex items-center justify-between px-2.5 text-xs uppercase font-semibold tracking-wider text-gray-500 mb-1 hover:text-white transition-colors duration-150 {{ request()->routeIs('billings.*') || request()->routeIs('invoices.*') ? 'text-white' : '' }}">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                                    <span>Billing</span>
                                </span>
                                <svg class="h-3 w-3 text-gray-500 transform transition-transform" :class="{ 'rotate-180': !openSections.billing }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="mt-1 space-y-1" x-show="openSections.billing" x-transition>
                                @can('manage billings')
                                <a href="{{ route('billings.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('billings.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Billings')) }}'.includes(lowerSearch)">
                                    {{ __('Billings') }}
                                </a>
                                @endcan
                                @can('manage invoices')
                                <a href="{{ route('invoices.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('invoices.*') ? $activeClasses : $inactiveClasses }}"
                                   x-show="!lowerSearch || '{{ strtolower(__('Invoices')) }}'.includes(lowerSearch)">
                                    {{ __('Invoices') }}
                                </a>
                                @endcan
                                @can('manage payments')
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'payments'.includes(lowerSearch)">Payments</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- Section: Reports --}}
                        @role('Admin')
                        <div x-show="!lowerSearch || 'Reports'.toLowerCase().includes(lowerSearch) || 'project costing'.includes(lowerSearch) || 'inventory levels'.includes(lowerSearch)">
                            <button type="button" @click="openSections.reports = !openSections.reports" class="w-full flex items-center justify-between px-2.5 text-xs uppercase font-semibold tracking-wider text-gray-500 mb-1 hover:text-white transition-colors duration-150">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <span>Reports</span>
                                </span>
                                <svg class="h-3 w-3 text-gray-500 transform transition-transform" :class="{ 'rotate-180': !openSections.reports }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="mt-1 space-y-1" x-show="openSections.reports" x-transition>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'project costing'.includes(lowerSearch)">Project Costing</a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'inventory levels'.includes(lowerSearch)">Inventory Levels</a>
                            </div>
                        </div>
                        
                        {{-- Section: Settings --}}
                        <div x-show="!lowerSearch || 'Settings'.toLowerCase().includes(lowerSearch) || 'users & roles'.includes(lowerSearch) || 'company details'.includes(lowerSearch) || 'manage roles'.includes(lowerSearch)">
                            <button type="button" @click="openSections.settings = !openSections.settings" class="w-full flex items-center justify-between px-2.5 text-xs uppercase font-semibold tracking-wider text-gray-500 mb-1 hover:text-white transition-colors duration-150 {{ request()->routeIs('users.*') || request()->routeIs('roles.*') ? 'text-white' : '' }}">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span>Settings</span>
                                </span>
                                <svg class="h-3 w-3 text-gray-500 transform transition-transform" :class="{ 'rotate-180': !openSections.settings }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="mt-1 space-y-1" x-show="openSections.settings" x-transition>
                                <a href="{{ route('users.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('users.*') ? $activeClasses : $inactiveClasses }}" 
                                   x-show="!lowerSearch || 'users & roles'.includes(lowerSearch)">
                                    Users & Roles
                                </a>
                                <a href="{{ route('roles.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('roles.*') ? $activeClasses : $inactiveClasses }}" 
                                   x-show="!lowerSearch || 'manage roles'.includes(lowerSearch)">
                                    Manage Roles
                                </a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'company details'.includes(lowerSearch)">Company Details</a>
                            </div>
                        </div>
                        @endrole
                    </nav>

                    <div class="flex-shrink-0 mt-6 pt-4 border-t border-slate-700/60">
                        <div class="px-2.5 py-2">
                            <div class="text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-400">{{ Auth::user()->email }}</div>
                        </div>
                        
                        <a href="{{ route('profile.edit') }}" 
                           class="{{ $baseClasses }} {{ request()->routeIs('profile.edit') ? $activeClasses : $inactiveClasses }}">
                            My Profile
                        </a>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}"
                               class="{{ $baseClasses }} {{ $inactiveClasses }}"
                               onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </a>
                        </form>
                    </div>
                </div> 
            </aside>
            
            {{-- === MAIN CONTENT === --}}
            <div class="flex-1 overflow-y-auto">
                
                @if (isset($header))
                    <header class="bg-white shadow sticky top-0 z-10">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <div class="p-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        
        @stack('scripts')
    </body>
</html>