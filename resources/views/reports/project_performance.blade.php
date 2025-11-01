<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Project Performance Report (S-Curve)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $project->quotation->project_name }}</h3>
                            <p class="text-sm text-gray-500">{{ $project->project_code }}</p>
                        </div>
                        <a href="{{ route('projects.show', $project) }}" class="text-sm text-blue-600 hover:underline">Back to Project</a>
                    </div>

                    {{-- 1. KPI Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        {{-- Cost Status --}}
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Cost Variance (CV)</h4>
                            <p class="text-3xl font-bold mt-2 {{ $reportData['cost_variance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ 'Rp ' . number_format($reportData['cost_variance'], 0, ',', '.') }}
                            </p>
                            @if ($reportData['cost_variance'] >= 0)
                                <p class="text-sm text-gray-600 mt-1">Under budget</p>
                            @else
                                <p class="text-sm text-gray-600 mt-1">Over budget</p>
                            @endif
                        </div>

                        {{-- Schedule Status --}}
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Schedule Variance (SV)</h4>
                            <p class="text-3xl font-bold mt-2 {{ $reportData['schedule_variance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ 'Rp ' . number_format($reportData['schedule_variance'], 0, ',', '.') }}
                            </p>
                            @if ($reportData['schedule_variance'] >= 0)
                                <p class="text-sm text-gray-600 mt-1">Ahead of schedule</p>
                            @else
                                <p class="text-sm text-gray-600 mt-1">Behind schedule</p>
                            @endif
                        </div>
                        
                        {{-- Progress Status --}}
                        <div class="bg-gray-50 p-6 rounded-lg shadow">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Progress</h4>
                            <div class="mt-2">
                                <p class="text-lg font-semibold text-blue-600">
                                    Planned: {{ number_format($reportData['planned_percent'], 2) }}%
                                </p>
                                <p class="text-lg font-semibold text-green-600">
                                    Actual: {{ number_format($reportData['earned_percent'], 2) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- 2. S-Curve Chart --}}
                    <div class="bg-gray-50 p-6 rounded-lg shadow">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Project S-Curve</h4>
                        <canvas id="sCurveChart"></canvas>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Time Schedule & Progress Detail</h4>
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">
                                                Task Description
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Bobot (%)
                                            </th>
                                            {{-- Loop through the weeks to create headers --}}
                                            @foreach($reportData['week_labels'] as $weekLabel)
                                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    {{ $weekLabel }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        {{-- Loop for Planned % --}}
                                        @foreach($reportData['task_details'] as $task)
                                            <tr class="bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 sticky left-0 bg-gray-50 z-10" rowspan="2">
                                                    {{ $task['name'] }}
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 text-right font-bold" rowspan="2">
                                                    {{ number_format($task['weight'], 2) }}%
                                                </td>
                                                {{-- Loop through weekly planned values --}}
                                                @foreach($task['weekly_planned'] as $weekValue)
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-600 text-right">
                                                        {{ $weekValue > 0 ? number_format($weekValue, 2) : '-' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                            {{-- Loop for Actual % --}}
                                            <tr>
                                                @foreach($task['weekly_actual'] as $weekValue)
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-green-600 text-right font-semibold">
                                                        {{ $weekValue > 0 ? number_format($weekValue, 2) : '-' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        
                                        {{-- Footer Row: Akumulasi Bobot Rencana (Planned) --}}
                                        <tr class="bg-gray-100 font-bold">
                                            <td class="px-6 py-3 text-left text-xs text-blue-600 uppercase tracking-wider sticky left-0 bg-gray-100 z-10" colspan="2">
                                                Akumulasi Bobot Rencana (Planned %)
                                            </td>
                                            @php $cumulative = 0; @endphp
                                            @foreach($reportData['chart_data']['labels'] as $index => $label)
                                                @php
                                                    $weeklyTotalPlanned = 0;
                                                    foreach($reportData['task_details'] as $task) {
                                                        $weeklyTotalPlanned += $task['weekly_planned'][$index];
                                                    }
                                                    $cumulative += $weeklyTotalPlanned;
                                                @endphp
                                                <td class="px-4 py-3 text-sm text-blue-600 text-right">
                                                    {{ number_format($cumulative, 2) }}
                                                </td>
                                            @endforeach
                                        </tr>
                                        {{-- Footer Row: Akumulasi Bobot Pelaksanaan (Actual) --}}
                                        <tr class="bg-gray-100 font-bold">
                                            <td class="px-6 py-3 text-left text-xs text-green-600 uppercase tracking-wider sticky left-0 bg-gray-100 z-10" colspan="2">
                                                Akumulasi Bobot Pelaksanaan (Actual %)
                                            </td>
                                            @php $cumulative = 0; @endphp
                                            @foreach($reportData['chart_data']['labels'] as $index => $label)
                                                @php
                                                    $weeklyTotalActual = 0;
                                                    foreach($reportData['task_details'] as $task) {
                                                        $weeklyTotalActual += $task['weekly_actual'][$index];
                                                    }
                                                    $cumulative += $weeklyTotalActual;
                                                @endphp
                                                <td class="px-4 py-3 text-sm text-green-600 text-right">
                                                    {{ number_format($cumulative, 2) }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('head-scripts')
        <script>
            // We need to wait for the DOM to be ready to render the chart
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('sCurveChart').getContext('2d');
                
                // Get data from PHP
                const chartData = @json($reportData['chart_data']);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: 'Planned Value (PV)',
                                data: chartData.planned,
                                borderColor: 'rgb(59, 130, 246)', // blue
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: false,
                                tension: 0.3,
                                pointRadius: 2
                            },
                            {
                                label: 'Earned Value (EV)',
                                data: chartData.earned,
                                borderColor: 'rgb(22, 163, 74)', // green
                                backgroundColor: 'rgba(22, 163, 74, 0.1)',
                                fill: false,
                                tension: 0.3,
                                pointRadius: 2
                            },
                            {
                                label: 'Actual Cost (AC)',
                                data: chartData.actual,
                                borderColor: 'rgb(220, 38, 38)', // red
                                backgroundColor: 'rgba(220, 38, 38, 0.1)',
                                fill: false,
                                tension: 0.3,
                                pointRadius: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Planned vs. Earned vs. Actual Cost'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                title: {
                                    display: true,
                                    text: 'Cumulative Cost (Rp)'
                                },
                                ticks: {
                                    callback: function(value, index, ticks) {
                                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Project Week'
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush

</x-app-layout>