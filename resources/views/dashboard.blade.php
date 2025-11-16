<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Executive Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @php
            // Helper function to "smartly" format currency KPIs
            function formatCurrencyKPI($value) {
                $absVal = abs($value);
                $sign = $value < 0 ? '-' : '';
                
                if ($absVal >= 1000000000) {
                    return $sign . '<span class="text-2xl align-top">Rp</span>' . number_format($absVal / 1000000000, 1) . '<span class="text-2xl">B</span>';
                } elseif ($absVal >= 1000000) {
                    return $sign . '<span class="text-2xl align-top">Rp</span>' . number_format($absVal / 1000000, 1) . '<span class="text-2xl">M</span>';
                } elseif ($absVal >= 1000) {
                    return $sign . '<span class="text-2xl align-top">Rp</span>' . number_format($absVal / 1000, 1) . '<span class="text-2xl">K</span>';
                }
                return $sign . '<span class="text-2xl align-top">Rp</span>' . number_format($absVal, 0);
            }
            @endphp
            
            {{-- 1. Premium KPI Cards [MODIFIED] --}}
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-6">
                
                {{-- Column 1: Cost Performance (NEW) --}}
                <a href="{{ route('projects.index') }}" 
                   class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 space-y-4 block hover:shadow-xl hover:-translate-y-1 transition-all duration-200">
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Cost Performance</h4>
                    <div class="flex items-baseline space-x-3">
                        <dd class="text-4xl font-semibold {{ $totalCostVariance >= 0 ? 'text-green-600' : 'text-red-600' }} tracking-tight">
                            {!! formatCurrencyKPI($totalCostVariance) !!}
                        </dd>
                        <dt class="font-medium text-gray-700">Total Cost Variance</dt>
                    </div>
                    <div class="flex items-baseline space-x-3">
                        <dd class="text-4xl font-semibold {{ $totalCpi >= 1 ? 'text-green-600' : 'text-red-600' }} tracking-tight">
                            {{ number_format($totalCpi, 2) }}
                        </dd>
                        <dt class="font-medium text-gray-700">Cost Perf. Index (CPI)</dt>
                    </div>
                    <p class="text-xs text-gray-500">
                        @if($totalCpi >= 1)
                            For every Rp1 spent, you are earning Rp{{ number_format($totalCpi, 2) }} of value.
                        @else
                            For every Rp1 spent, you are only earning Rp{{ number_format($totalCpi, 2) }} of value.
                        @endif
                    </p>
                </a>

                {{-- Column 2: Financials (Clickable) --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 space-y-4">
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Financials</h4>
                    <a href="{{ route('projects.index') }}" 
                       class="flex items-baseline space-x-3 group hover:opacity-70 transition-opacity">
                        <dd class="text-4xl font-semibold text-blue-600 tracking-tight">
                            {!! formatCurrencyKPI($totalActiveProjectValue) !!}
                        </dd>
                        <dt class="font-medium text-gray-700 group-hover:underline">Value of Active Projects</dt>
                    </a>
                    <a href="{{ route('invoices.index') }}" 
                       class="flex items-baseline space-x-3 group hover:opacity-70 transition-opacity">
                        <dd class="text-4xl font-semibold text-yellow-600 tracking-tight">
                            {!! formatCurrencyKPI($outstandingInvoices) !!}
                        </dd>
                        <dt class="font-medium text-gray-700 group-hover:underline">Outstanding Invoices</dt>
                    </a>
                </div>

                {{-- Column 3: Operations (Not Clickable) --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 space-y-4">
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Operations</h4>
                    <div class="flex items-baseline space-x-3">
                        <dd class="text-4xl font-semibold text-red-600 tracking-tight">{{ $pendingRequests->count() }}</dd>
                        <dt class="font-medium text-gray-700">Pending Material Requests</dt>
                    </div>
                    <div class="flex items-baseline space-x-3">
                        <dd class="text-4xl font-semibold text-yellow-600 tracking-tight">{{ $procurementBacklogCount }}</dd>
                        <dt class="font-medium text-gray-700">Procurement Backlog</dt>
                    </div>
                    <div class="flex items-baseline space-x-3">
                        <dd class="text-4xl font-semibold text-orange-600 tracking-tight">{{ $latePurchaseOrders->count() }}</dd>
                        <dt class="font-medium text-gray-700">Late POs</dt>
                    </div>
                </div>

            </div>

            {{-- 2. New Chart: Cost Variance by Project --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Profit/Loss (Cost Variance)</h3>
                    <p class="text-sm text-gray-600 -mt-4 mb-4">Positive (Green) is profit. Negative (Red) is loss.</p>
                    <div class="h-64">
                        <canvas id="costVarianceChart"></canvas>
                    </div>
                </div>

                {{-- 3. Financial Chart --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">6-Month Cash Flow (Invoiced vs. Paid)</h3>
                    <div class="h-64">
                        <canvas id="cashFlowChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- 4. Action Item Lists --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                {{-- Pending Material Requests List --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 flex flex-col" style="max-height: 400px;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Action Required: Material Requests</h3>
                    <div class="flex-1 overflow-y-auto">
                        <ul class="divide-y divide-gray-200">
                            @forelse($pendingRequests as $request)
                                <li class="p-3 hover:bg-gray-50 transition duration-150">
                                    <a href="{{ route('material-requests.show', $request) }}" class="block">
                                        <div class="flex justify-between items-center">
                                            <span class="font-semibold text-indigo-700">{{ $request->request_code }}</span>
                                            <span class="text-sm text-gray-500">{{ $request->request_date->format('d-M-Y') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-800 mt-1">{{ $request->project->quotation->project_name }}</p>
                                        <p class="text-xs text-gray-500">Requested by {{ $request->requester->name }}</p>
                                    </a>
                                </li>
                            @empty
                                <li class="p-4 text-sm text-gray-500 text-center">
                                    No pending material requests. Good job!
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Late Purchase Orders List --}}
                <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100 flex flex-col" style="max-height: 400px;">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">At Risk: Late Purchase Orders</h3>
                    <div class="flex-1 overflow-y-auto">
                        <ul class="divide-y divide-gray-200">
                            @forelse($latePurchaseOrders as $po)
                                <li class="p-3 hover:bg-gray-50 transition duration-150">
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="block">
                                        <div class="flex justify-between items-center">
                                            <span class="font-semibold text-red-600">{{ $po->po_number }}</span>
                                            <span class="text-sm text-red-600 font-medium">
                                                Due: {{ $po->expected_delivery_date->format('d-M-Y') }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-800 mt-1">Supplier: {{ $po->supplier->name }}</p>
                                    </a>
                                </li>
                            @empty
                                <li class="p-4 text-sm text-gray-500 text-center">
                                    No late purchase orders.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            {{-- 5. Active Projects Rich Table --}}
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Active Project Health</h3>
                <div class="mt-4 border rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 200px;">Progress (Actual vs. Planned)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget vs. Actual (Rp)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($activeProjects as $project)
                                @php
                                    $isDelayed = $project->actual_progress < $project->planned_progress;
                                    $isOverBudget = $project->cost_variance < 0; // Use the CV we already calculated
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <a
                                            href="{{ route('reports.project_performance', $project) }}" class="transition duration-150">
                                        <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-600">{{ $project->quotation->project_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $project->project_code }}</div>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $project->client->name }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 relative">
                                            {{-- Planned progress marker --}}
                                            @if($project->planned_progress > 0)
                                            <div class="absolute h-4 w-1 bg-red-500 -top-1 rounded" 
                                                 style="left: calc({{ $project->planned_progress }}% - 2px);" 
                                                 title="Planned: {{ number_format($project->planned_progress, 1) }}%">
                                            </div>
                                            @endif
                                            {{-- Actual progress bar --}}
                                            <div class="bg-indigo-600 h-2.5 rounded-full" 
                                                 style="width: {{ $project->actual_progress }}%"
                                                 title="Actual: {{ number_format($project->actual_progress, 1) }}%">
                                            </div>
                                        </div>
                                        <div class="text-xs font-semibold text-indigo-600 text-right mt-1">
                                            {{ number_format($project->actual_progress, 1) }}%
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        <div class="font-medium {{ $isOverBudget ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ number_format($project->actual_cost, 0) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            / {{ number_format($project->total_budget, 0) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        @if($isDelayed && $project->planned_progress > $project->actual_progress)
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Delayed
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                On Track
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">No active projects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Helper to format currency
            const formatAsIDR = (value) => {
                if (value >= 1_000_000_000) return 'Rp ' + (value / 1_000_000_000).toFixed(1) + 'B';
                if (value >= 1_000_000) return 'Rp ' + (value / 1_000_000).toFixed(1) + 'M';
                if (value >= 1_000) return 'Rp ' + (value / 1_000).toFixed(1) + 'K';
                return 'Rp ' + value;
            };

            // 1. Cash Flow Chart
            const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
            const cashFlowData = @json($cashFlowChartData);
            new Chart(cashFlowCtx, {
                type: 'bar',
                data: cashFlowData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { callback: (value) => formatAsIDR(value) } }, x: { grid: { display: false } } },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.dataset.label || ''}: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.y)}`
                            }
                        }
                    }
                }
            });

            // 2. Cost Variance Chart
            const cvCtx = document.getElementById('costVarianceChart').getContext('2d');
            const cvData = @json($costVarianceChartData);
            new Chart(cvCtx, {
                type: 'bar',
                data: {
                    labels: cvData.labels,
                    datasets: [{
                        label: 'Cost Variance (Profit/Loss)',
                        data: cvData.data,
                        // Dynamic coloring: red for loss, green for profit
                        backgroundColor: (context) => {
                            const value = context.raw;
                            return value < 0 ? 'rgba(220, 38, 38, 0.8)' : 'rgba(5, 150, 105, 0.8)';
                        },
                        borderColor: (context) => {
                            const value = context.raw;
                            return value < 0 ? 'rgba(220, 38, 38, 1)' : 'rgba(5, 150, 105, 1)';
                        },
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Makes it a horizontal bar chart
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { 
                        x: { 
                            beginAtZero: true, 
                            ticks: { callback: (value) => formatAsIDR(value) } 
                        },
                        y: { 
                            grid: { display: false } 
                        } 
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `Cost Variance: ${new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.x)}`
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
