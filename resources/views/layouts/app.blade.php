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
        <div class="min-h-screen bg-gray-100">

            <main>
                <div class="flex">

                    <aside class="w-64 bg-white shadow-md min-h-screen p-4 flex-shrink-0 flex flex-col">
                        @php
                        // Define consistent classes for all sidebar links
                        $baseClasses = 'block w-full text-left px-4 py-2 text-sm rounded hover:bg-gray-100 transition duration-150';
                        $activeClasses = 'bg-gray-100 font-semibold text-gray-900';
                        $inactiveClasses = 'text-gray-700 hover:text-gray-900';
                        $deadLinkClasses = 'text-gray-400 cursor-not-allowed';
                        @endphp
                        
                        {{-- This nav block will grow to fill the available space --}}
                        <nav class="space-y-4 flex-grow">

                            <div>
                                <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Main</h3>
                                <a href="{{ route('dashboard') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('dashboard') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Dashboard') }}
                                </a>
                            </div>

                            <div>
                                <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Sales</h3>
                                <a href="{{ route('clients.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('clients.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Clients') }}
                                </a>
                                <a href="{{ route('quotations.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('quotations.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Quotations') }}
                                </a>
                            </div>

                            <div>
                                <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Projects</h3>
                                <a href="{{ route('projects.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('projects.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Project List') }}
                                </a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}">Project Tasks (WBS)</a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}">Progress Updates</a>
                            </div>

                            <div>
                                <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Procurement</h3>
                                <a href="{{ route('suppliers.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('suppliers.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Suppliers') }}
                                </a>
                                <a href="{{ route('purchase-orders.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('purchase-orders.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Purchase Orders') }}
                                </a> 
                            </div>

                            <div>
                                <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Work Library</h3>
                                <a href="{{ route('ahs-library.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('ahs-library.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('AHS Library') }}
                                </a>
                                <a href="{{ route('work-types.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('work-types.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Work Types') }}
                                </a>
                                <a href="{{ route('work-items.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('work-items.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Work Items') }}
                                </a>
                            </div>
                            
                            <div>
                                <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Inventory</h3>
                                <a href="{{ route('inventory-items.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('inventory-items.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Item Master') }}
                                </a>
                                <a href="{{ route('item-categories.index') }}" 
                                   class="{{ $baseClasses }} {{ request()->routeIs('item-categories.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Item Categories') }}
                                </a>
                                <a href="{{ route('equipment.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('equipment.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Equipment') }}
                                </a>
                                <a href="{{ route('labor-rates.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('labor-rates.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Labor Rates') }}
                                </a>
                                <a href="{{ route('stock-ledger.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('stock-ledger.index') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Stock Ledger') }}
                                </a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}">Material Usage</a>
                            </div>

                            <div>
                                <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Billing</h3>
                                <a href="{{ route('billings.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('billings.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Billings') }}
                                </a>
                                <a href="{{ route('invoices.index') }}"
                                   class="{{ $baseClasses }} {{ request()->routeIs('invoices.*') ? $activeClasses : $inactiveClasses }}">
                                    {{ __('Invoices') }}
                                </a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}">Payments</a>
                            </div>

                            <div>
                                <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Reports</h3>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}">Project Costing</a>
                                <a href="#" class="{{ $baseClasses }} {{ $deadLinkClasses }}">Inventory Levels</a>
                            </div>
                        </nav>
                        
                        {{-- === NEW USER MENU (Sticks to the bottom) === --}}
                        <div class="flex-shrink-0 mt-6 pt-4 border-t border-gray-200">
                            <div class="px-4 py-2">
                                <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                            </div>
                            
                            <a href="{{ route('profile.edit') }}" 
                               class="{{ $baseClasses }} {{ request()->routeIs('profile.edit') ? $activeClasses : $inactiveClasses }}">
                                {{ __('My Profile') }}
                            </a>
            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}"
                                   class="{{ $baseClasses }} {{ $inactiveClasses }}"
                                   onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </a>
                            </form>
                        </div>
                        
                    </aside>
                    
                    <div class="flex-1">
                        
                        {{-- The Page Header ($header slot) is now here --}}
                        @if (isset($header))
                            <header class="bg-white shadow">
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
            </main>
        </div>
        
        @stack('scripts')
    </body>
</html>