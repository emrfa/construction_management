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

            @include('layouts.navigation')

            <main>
                <div class="flex">

                    <aside class="w-64 bg-white shadow-md min-h-screen p-4">
                            <nav class="space-y-4">

                                <div>
                                    <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Main</h3>
                                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="block w-full text-left">
                                        {{ __('Dashboard') }}
                                    </x-nav-link>
                                </div>

                                <div>
                                    <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Sales</h3>
                                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200">
                                        {{ __('Clients') }}
                                    </x-nav-link>
                                    <a href="{{ route('quotations.index') }}" 
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('quotations.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Quotations') }}
                                    </a>
                                </div>

                                <div>
                                    <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Projects</h3>
                                    <a href="{{ route('projects.index') }}" 
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('projects.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Project List') }}
                                    </a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200">Project Tasks (WBS)</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200">Progress Updates</a>
                                </div>

                                <div>
                                    <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Procurement</h3>
                                    <a href="{{ route('suppliers.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('suppliers.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Suppliers') }}
                                    </a>
                                    <a href="{{ route('purchase-orders.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('purchase-orders.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Purchase Orders') }}
                                    </a> 
                                </div>

                                <div>
                                    <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Work Library</h3>
                                    <a href="{{ route('ahs-library.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('ahs-library.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('AHS Library') }}
                                    </a>
                                    <a href="{{ route('work-types.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('work-types.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Work Types') }}
                                    </a>
                                    <a href="{{ route('work-items.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('work-items.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Work Items') }}
                                    </a>
                                </div>
                                <div>
                                    <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Inventory</h3>
                                    <a href="{{ route('inventory-items.index') }}" 
                                        class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('inventory-items.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                            {{ __('Item Master') }}
                                    </a>
                                    <a href="{{ route('equipment.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('equipment.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Equipment') }}
                                    </a>
                                    <a href="{{ route('labor-rates.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('labor-rates.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Labor Rates') }}
                                    </a>
                                    <a href="{{ route('stock-ledger.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('stock-ledger.index') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Stock Ledger') }}
                                    </a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200">Material Usage</a>
                                </div>

                                <div>
                                    <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Billing</h3>
                                    <a href="{{ route('billings.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('billings.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Billings') }}
                                    </a>
                                    <a href="{{ route('invoices.index') }}"
                                    class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200 {{ request()->routeIs('invoices.*') ? 'bg-gray-100 font-semibold' : '' }}">
                                        {{ __('Invoices') }}
                                    </a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200">Payments</a>
                                </div>

                                <div>
                                    <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Reports</h3>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200">Project Costing</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200">Inventory Levels</a>
                                </div>

                                <div>
                                    <h3 class="text-xs uppercase text-gray-500 font-bold mb-2">Settings</h3>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200">Users & Roles</a>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 rounded hover:bg-gray-200">Company Details</a>
                                </div>

                            </nav>
                        </aside>
                    <div class="flex-1 p-6">

                        @if (isset($header))
                            <header class="bg-white shadow">
                                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                    {{ $header }}
                                </div>
                            </header>
                        @endif

                        {{ $slot }}
                    </div>
                    </div>
            </main>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
        @stack('scripts')
    </body>
</html>