<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Construction Manager') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
        
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        @stack('head-scripts')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="h-screen bg-gray-100 flex">
            {{-- === SIDEBAR === --}}
            <aside class="w-64 bg-white shadow-md p-4 flex-shrink-0 flex flex-col h-full" x-data="{ searchTerm: '', get lowerSearch() { return this.searchTerm.toLowerCase() } }">
                
                <div class="pb-4 mb-4 border-b border-gray-200 flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center justify-center">
                        <x-application-logo class="block h-16 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="pb-4 mb-4 border-b border-gray-200 flex-shrink-0">
                    <form onsubmit="return false;"> <x-input-label for="sidebar-search" class="sr-only">Search</x-input-label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none" aria-hidden="true">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <x-text-input id="sidebar-search" class="block mt-1 w-full pl-10" type="text" name="search" placeholder="Search..." x-model.debounce.300ms="searchTerm" />
                        </div>
                    </form>
                </div>
                
                @php
                // Define consistent classes for all sidebar links
                $baseClasses = 'block w-full text-left px-4 py-2 text-sm rounded hover:bg-gray-100 transition duration-150';
                $activeClasses = 'bg-gray-100 font-semibold text-gray-900';
                $inactiveClasses = 'text-gray-700 hover:text-gray-900';
                $deadLinkClasses = 'text-gray-400 cursor-not-allowed';
                @endphp
                
                <div class="flex-1 overflow-y-auto">
                    <nav class="space-y-4">
                        <div x-show="!lowerSearch || 'Main'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Dashboard')) }}'.includes(lowerSearch)">
                            <h3 class="text-xs uppercase text-gray-500 font-bold mb-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                <span>Main</span>
                            </h3>
                            <a href="{{ route('dashboard') }}" 
                               class="{{ $baseClasses }} {{ request()->routeIs('dashboard') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Dashboard')) }}'.includes(lowerSearch)">
                                {{ __('Dashboard') }}
                            </a>
                        </div>

                        <div x-show="!lowerSearch || 'Sales'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Clients')) }}'.includes(lowerSearch) || '{{ strtolower(__('Quotations')) }}'.includes(lowerSearch)">
                            <h3 class="text-xs uppercase text-gray-500 font-bold mb-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                <span>Sales</span>
                            </h3>
                            <a href="{{ route('clients.index') }}" 
                               class="{{ $baseClasses }} {{ request()->routeIs('clients.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Clients')) }}'.includes(lowerSearch)">
                                {{ __('Clients') }}
                            </a>
                            <a href="{{ route('quotations.index') }}" 
                               class="{{ $baseClasses }} {{ request()->routeIs('quotations.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Quotations')) }}'.includes(lowerSearch)">
                                {{ __('Quotations') }}
                            </a>
                        </div>

                        <div x-show="!lowerSearch || 'Projects'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Project List')) }}'.includes(lowerSearch) || 'project tasks (wbs)'.includes(lowerSearch) || 'progress updates'.includes(lowerSearch)">
                            <h3 class="text-xs uppercase text-gray-500 font-bold mb-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
                                <span>Projects</span>
                            </h3>
                            <a href="{{ route('projects.index') }}" 
                               class="{{ $baseClasses }} {{ request()->routeIs('projects.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Project List')) }}'.includes(lowerSearch)">
                                {{ __('Project List') }}
                            </a>
                            <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'project tasks (wbs)'.includes(lowerSearch)">Project Tasks (WBS)</a>
                            <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'progress updates'.includes(lowerSearch)">Progress Updates</a>
                        </div>

                        <div x-show="!lowerSearch || 'Procurement'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Suppliers')) }}'.includes(lowerSearch) || '{{ strtolower(__('Purchase Orders')) }}'.includes(lowerSearch)">
                            <h3 class="text-xs uppercase text-gray-500 font-bold mb-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                <span>Procurement</span>
                            </h3>
                            <a href="{{ route('suppliers.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('suppliers.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Suppliers')) }}'.includes(lowerSearch)">
                                {{ __('Suppliers') }}
                            </a>
                            <a href="{{ route('purchase-orders.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('purchase-orders.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Purchase Orders')) }}'.includes(lowerSearch)">
                                {{ __('Purchase Orders') }}
                            </a> 
                        </div>

                        <div x-show="!lowerSearch || 'Work Library'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('AHS Library')) }}'.includes(lowerSearch) || '{{ strtolower(__('Work Types')) }}'.includes(lowerSearch) || '{{ strtolower(__('Work Items')) }}'.includes(lowerSearch)">
                            <h3 class="text-xs uppercase text-gray-500 font-bold mb-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                <span>Work Library</span>
                            </h3>
                            <a href="{{ route('ahs-library.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('ahs-library.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('AHS Library')) }}'.includes(lowerSearch)">
                                {{ __('AHS Library') }}
                            </a>
                            <a href="{{ route('work-types.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('work-types.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Work Types')) }}'.includes(lowerSearch)">
                                {{ __('Work Types') }}
                            </a>
                            <a href="{{ route('work-items.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('work-items.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Work Items')) }}'.includes(lowerSearch)">
                                {{ __('Work Items') }}
                            </a>
                        </div>
                        
                        <div x-show="!lowerSearch || 'Inventory'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Item Master')) }}'.includes(lowerSearch) || '{{ strtolower(__('Item Categories')) }}'.includes(lowerSearch) || '{{ strtolower(__('Equipment')) }}'.includes(lowerSearch) || '{{ strtolower(__('Labor Rates')) }}'.includes(lowerSearch) || '{{ strtolower(__('Stock Ledger')) }}'.includes(lowerSearch) || 'material usage'.includes(lowerSearch)">
                            <h3 class="text-xs uppercase text-gray-500 font-bold mb-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                <span>Inventory</span>
                            </h3>
                            <a href="{{ route('inventory-items.index') }}" 
                               class="{{ $baseClasses }} {{ request()->routeIs('inventory-items.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Item Master')) }}'.includes(lowerSearch)">
                                {{ __('Item Master') }}
                            </a>
                            <a href="{{ route('item-categories.index') }}" 
                               class="{{ $baseClasses }} {{ request()->routeIs('item-categories.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Item Categories')) }}'.includes(lowerSearch)">
                                {{ __('Item Categories') }}
                            </a>
                            <a href="{{ route('equipment.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('equipment.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Equipment')) }}'.includes(lowerSearch)">
                                {{ __('Equipment') }}
                            </a>
                            <a href="{{ route('labor-rates.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('labor-rates.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Labor Rates')) }}'.includes(lowerSearch)">
                                {{ __('Labor Rates') }}
                            </a>
                            <a href="{{ route('stock-ledger.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('stock-ledger.index') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Stock Ledger')) }}'.includes(lowerSearch)">
                                {{ __('Stock Ledger') }}
                            </a>
                            <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'material usage'.includes(lowerSearch)">Material Usage</a>
                        </div>

                        <div x-show="!lowerSearch || 'Billing'.toLowerCase().includes(lowerSearch) || '{{ strtolower(__('Billings')) }}'.includes(lowerSearch) || '{{ strtolower(__('Invoices')) }}'.includes(lowerSearch) || 'payments'.includes(lowerSearch)">
                            <h3 class="text-xs uppercase text-gray-500 font-bold mb-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                                <span>Billing</span>
                            </h3>
                            <a href="{{ route('billings.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('billings.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Billings')) }}'.includes(lowerSearch)">
                                {{ __('Billings') }}
                            </a>
                            <a href="{{ route('invoices.index') }}"
                               class="{{ $baseClasses }} {{ request()->routeIs('invoices.*') ? $activeClasses : $inactiveClasses }}"
                               x-show="!lowerSearch || '{{ strtolower(__('Invoices')) }}'.includes(lowerSearch)">
                                {{ __('Invoices') }}
                            </a>
                            <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'payments'.includes(lowerSearch)">Payments</a>
                        </div>

                        <div x-show="!lowerSearch || 'Reports'.toLowerCase().includes(lowerSearch) || 'project costing'.includes(lowerSearch) || 'inventory levels'.includes(lowerSearch)">
                            <h3 class="text-xs uppercase text-gray-500 font-bold mb-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span>Reports</span>
                            </h3>
                            <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'project costing'.includes(lowerSearch)">Project Costing</a>
                            <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'inventory levels'.includes(lowerSearch)">Inventory Levels</a>
                        </div>

                        <div x-show="!lowerSearch || 'Settings'.toLowerCase().includes(lowerSearch) || 'users & roles'.includes(lowerSearch) || 'company details'.includes(lowerSearch)">
                            <h3 class="text-xs uppercase text-gray-500 font-bold mb-2 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span>Settings</span>
                            </h3>
                            <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'users & roles'.includes(lowerSearch)">Users & Roles</a>
                            <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}" x-show="!lowerSearch || 'company details'.includes(lowerSearch)">Company Details</a>
                        </div>
                    </nav>

                    <div class="flex-shrink-0 mt-6 pt-4 border-t border-gray-200">
                        <div class="px-4 py-2">
                            <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
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
                </div> </aside>
            
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