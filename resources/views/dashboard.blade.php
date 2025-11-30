<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Executive Overview') }}
            </h2>
            <span class="text-sm text-gray-500">Last updated: {{ now()->format('d M Y, H:i') }}</span>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @php
            // Helper function to "smartly" format currency KPIs
            function formatCurrencyKPI($value) {
                $absVal = abs($value);
                $sign = $value < 0 ? '-' : '';
                
                if ($absVal >= 1000000000) {
                    return $sign . '<span class="text-lg align-top text-gray-500 font-normal">Rp</span>' . number_format($absVal / 1000000000, 1) . '<span class="text-lg text-gray-500 font-normal">B</span>';
                } elseif ($absVal >= 1000000) {
                    return $sign . '<span class="text-lg align-top text-gray-500 font-normal">Rp</span>' . number_format($absVal / 1000000, 1) . '<span class="text-lg text-gray-500 font-normal">M</span>';
                } elseif ($absVal >= 1000) {
                    return $sign . '<span class="text-lg align-top text-gray-500 font-normal">Rp</span>' . number_format($absVal / 1000, 1) . '<span class="text-lg text-gray-500 font-normal">K</span>';
                }
                return $sign . '<span class="text-lg align-top text-gray-500 font-normal">Rp</span>' . number_format($absVal, 0);
            }
            @endphp

            {{-- 1. CEO SNAPSHOT --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                {{-- Card 1: Total Active Value --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Active Projects Value</p>
                            <h3 class="mt-2 text-3xl font-bold text-gray-900">
                                {!! formatCurrencyKPI($totalActiveProjectValue) !!}
                            </h3>
                        </div>
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-gray-500">{{ $activeProjectsCount }} active projects</span>
                    </div>
                </div>

                {{-- Card 2: Net Project Profit (Total CV) --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Net Project Profit (CV)</p>
                            <h3 class="mt-2 text-3xl font-bold {{ $totalCostVariance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {!! formatCurrencyKPI($totalCostVariance) !!}
                            </h3>
                        </div>
                        <div class="p-2 {{ $totalCostVariance >= 0 ? 'bg-green-50' : 'bg-red-50' }} rounded-lg">
                            <svg class="w-6 h-6 {{ $totalCostVariance >= 0 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="font-medium {{ $totalCpi >= 1 ? 'text-green-600' : 'text-red-600' }}">CPI: {{ number_format($totalCpi, 2) }}</span>
                        <span class="mx-2 text-gray-300">|</span>
                        <span class="text-gray-500">Efficiency Index</span>
                    </div>
                </div>

                {{-- Card 3: Outstanding Invoices --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Outstanding Invoices</p>
                            <h3 class="mt-2 text-3xl font-bold text-gray-900">
                                {!! formatCurrencyKPI($outstandingInvoices) !!}
                            </h3>
                        </div>
                        <div class="p-2 bg-yellow-50 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        @if($outstandingTrend > 0)
                            <span class="text-red-600 flex items-center font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                {{ number_format(abs($outstandingTrend), 1) }}%
                            </span>
                            <span class="ml-2 text-gray-500">vs last month</span>
                        @elseif($outstandingTrend < 0)
                            <span class="text-green-600 flex items-center font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                                {{ number_format(abs($outstandingTrend), 1) }}%
                            </span>
                            <span class="ml-2 text-gray-500">vs last month</span>
                        @else
                            <span class="text-gray-500">No change vs last month</span>
                        @endif
                    </div>
                </div>

                {{-- Card 4: Risk Summary --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Projects at Risk</p>
                            <h3 class="mt-2 text-3xl font-bold {{ ($projectsOverBudgetCount + $projectsDelayedCount) > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $projectsOverBudgetCount + $projectsDelayedCount }}
                            </h3>
                        </div>
                        <div class="p-2 {{ ($projectsOverBudgetCount + $projectsDelayedCount) > 0 ? 'bg-red-50' : 'bg-green-50' }} rounded-lg">
                            <svg class="w-6 h-6 {{ ($projectsOverBudgetCount + $projectsDelayedCount) > 0 ? 'text-red-600' : 'text-green-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-sm">
                        <span class="text-gray-500">Requires attention</span>
                    </div>
                </div>
            </div>

            {{-- 2. RISK HIGHLIGHTS --}}
            @if($projectsOverBudgetCount > 0 || $projectsDelayedCount > 0 || $overdueInvoicesCount > 0 || $latePurchaseOrders->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @if($projectsOverBudgetCount > 0)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg flex items-center justify-between">
                    <div>
                        <p class="text-red-800 font-bold">{{ $projectsOverBudgetCount }} Projects</p>
                        <p class="text-red-600 text-xs">Over Budget</p>
                    </div>
                    <a href="{{ route('projects.index', ['filter' => 'over_budget']) }}" class="text-red-600 hover:text-red-800 text-sm font-medium">View &rarr;</a>
                </div>
                @endif

                @if($projectsDelayedCount > 0)
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-lg flex items-center justify-between">
                    <div>
                        <p class="text-orange-800 font-bold">{{ $projectsDelayedCount }} Projects</p>
                        <p class="text-orange-600 text-xs">Behind Schedule</p>
                    </div>
                    <a href="{{ route('projects.index', ['filter' => 'delayed']) }}" class="text-orange-600 hover:text-orange-800 text-sm font-medium">View &rarr;</a>
                </div>
                @endif

                @if($overdueInvoicesCount > 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg flex items-center justify-between">
                    <div>
                        <p class="text-yellow-800 font-bold">{{ $overdueInvoicesCount }} Invoices</p>
                        <p class="text-yellow-600 text-xs">Overdue ({!! formatCurrencyKPI($overdueInvoicesSum) !!})</p>
                    </div>
                    <a href="{{ route('invoices.index') }}" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">View &rarr;</a>
                </div>
                @endif

                @if($latePurchaseOrders->count() > 0)
                <div class="bg-gray-100 border-l-4 border-gray-500 p-4 rounded-r-lg flex items-center justify-between">
                    <div>
                        <p class="text-gray-800 font-bold">{{ $latePurchaseOrders->count() }} POs</p>
                        <p class="text-gray-600 text-xs">Late Delivery</p>
                    </div>
                    <a href="{{ route('purchase-orders.index') }}" class="text-gray-600 hover:text-gray-800 text-sm font-medium">View &rarr;</a>
                </div>
                @endif
            </div>
            @endif

            {{-- 3. TOP PROJECTS WIDGET & CHARTS --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Column 1: Top 5 Projects by Profit/Loss --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Top Performance (Profit/Loss)</h3>
                    <div class="space-y-4">
                        @foreach($topProjectsByProfit as $project)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition cursor-pointer" onclick="window.location='{{ route('reports.project_performance', $project) }}'">
                                <div class="flex-1 min-w-0 mr-4">
                                    <p class="text-sm font-semibold text-gray-900 truncate" title="{{ $project->quotation->project_name }}">
                                        {{ $project->quotation->project_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $project->project_code }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold {{ $project->cost_variance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {!! formatCurrencyKPI($project->cost_variance) !!}
                                    </p>
                                    <p class="text-xs text-gray-500">Variance</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                         <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 font-medium hover:text-indigo-800">View All Projects</a>
                    </div>
                </div>

                {{-- Column 2 & 3: Charts --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Cash Flow Chart --}}
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900">Cash Flow (6 Months)</h3>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded bg-blue-100 text-blue-800">Invoiced vs Paid</span>
                        </div>
                        <div class="h-64">
                            <canvas id="cashFlowChart"></canvas>
                        </div>
                    </div>

                    {{-- Cost Variance Chart --}}
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900">Project Cost Variance</h3>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded bg-gray-100 text-gray-800">Profit vs Loss</span>
                        </div>
                        <div class="h-64">
                            <canvas id="costVarianceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. SIMPLIFIED ACTIVE PROJECTS TABLE --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Active Projects Health</h3>
                    <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 font-medium hover:text-indigo-800">View All Projects &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Financials</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($activeProjects as $project)
                                @php
                                    $isDelayed = $project->actual_progress < $project->planned_progress;
                                    $isOverBudget = $project->cost_variance < 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition cursor-pointer group" onclick="window.location='{{ route('reports.project_performance', $project) }}'">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="ml-0">
                                                <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 transition">{{ $project->quotation->project_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $project->client->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="w-32">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="font-medium text-gray-700">{{ number_format($project->actual_progress, 1) }}%</span>
                                                <span class="text-gray-500">Target: {{ number_format($project->planned_progress, 1) }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $project->actual_progress }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-medium">
                                            {!! formatCurrencyKPI($project->cost_variance) !!}
                                        </div>
                                        <div class="text-xs text-gray-500">Variance</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($isDelayed)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                Delayed
                                            </span>
                                        @elseif($isOverBudget)
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Over Budget
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                On Track
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No active projects found.</td>
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
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            ticks: { callback: (value) => formatAsIDR(value) },
                            grid: { borderDash: [2, 4], color: '#f3f4f6' }
                        }, 
                        x: { 
                            grid: { display: false } 
                        } 
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 8,
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
                        backgroundColor: (context) => {
                            const value = context.raw;
                            return value < 0 ? 'rgba(239, 68, 68, 0.8)' : 'rgba(16, 185, 129, 0.8)';
                        },
                        borderRadius: 4,
                        barThickness: 20,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { 
                        x: { 
                            beginAtZero: true, 
                            ticks: { callback: (value) => formatAsIDR(value) },
                            grid: { borderDash: [2, 4], color: '#f3f4f6' }
                        },
                        y: { 
                            grid: { display: false } 
                        } 
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            cornerRadius: 8,
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
