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
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Schedule Variance (%)</h4>
                            <p class="text-3xl font-bold mt-2 {{ $reportData['schedule_variance_percent'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ ($reportData['schedule_variance_percent'] >= 0 ? '+' : '') . number_format($reportData['schedule_variance_percent'], 2) . '%' }}
                            </p>
                            @if ($reportData['schedule_variance_percent'] >= 0)
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
                    <div class="bg-gray-50 p-6 rounded-lg shadow" x-data="{ mode: 'percent' }">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-gray-800">Project S-Curve</h4>
                            {{-- Toggle Switch --}}
                            <div class="bg-gray-200 p-1 rounded-lg inline-flex">
                                <button 
                                    @click="mode = 'percent'; updateChartMode('percent')"
                                    :class="{ 'bg-white shadow text-gray-900': mode === 'percent', 'text-gray-500 hover:text-gray-900': mode !== 'percent' }"
                                    class="px-3 py-1 text-sm font-medium rounded-md transition-all duration-200">
                                    Percentage (%)
                                </button>
                                <button 
                                    @click="mode = 'cost'; updateChartMode('cost')"
                                    :class="{ 'bg-white shadow text-gray-900': mode === 'cost', 'text-gray-500 hover:text-gray-900': mode !== 'cost' }"
                                    class="px-3 py-1 text-sm font-medium rounded-md transition-all duration-200">
                                    Cost (Rp)
                                </button>
                            </div>
                        </div>
                        <canvas id="sCurveChart"></canvas>
                    </div>

                    <div class="mt-8">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-gray-800">Time Schedule & Progress Detail</h4>
                            <button onclick="downloadSchedulePDF()" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm">
                                <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/></svg>
                                <span>Download Schedule</span>
                            </button>
                        </div>
                        <div id="pdf-export-container">
                            {{-- PDF Header (Hidden on Screen) --}}
                            <div id="pdf-header" class="hidden mb-8">
                                <div class="flex justify-between items-start border-b-2 border-gray-800 pb-4">
                                    <div>
                                        <h1 class="text-2xl font-bold text-gray-900">PROJECT TIME SCHEDULE</h1>
                                        <p class="text-sm text-gray-600">Generated on: {{ date('d M Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <h2 class="text-xl font-bold">{{ $project->quotation->project_name }}</h2>
                                    </div>
                                </div>
                            </div>

                            <div id="scheduleTableContainer" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
                            <div class="overflow-x-auto relative">
                                {{-- Overlay Chart Canvas --}}
                                <canvas id="overlaySCurve" class="absolute top-0 left-0 pointer-events-none z-20"></canvas>
                                
                                <table id="scheduleTable" class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-50 z-30">
                                                Task Description
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Bobot (%)
                                            </th>
                                            {{-- Loop through the weeks to create headers --}}
                                            @foreach($reportData['week_labels'] as $weekLabel)
                                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 100px;">
                                                    {{ $weekLabel }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        {{-- Loop for Planned % --}}
                                        @foreach($reportData['task_details'] as $task)
                                            <tr class="bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 sticky left-0 bg-gray-50 z-30" rowspan="2">
                                                    {{ $task['name'] }}
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 text-right font-bold" rowspan="2">
                                                    {{ number_format($task['weight'], 2) }}%
                                                </td>
                                                {{-- Loop through weekly planned values --}}
                                                @foreach($task['weekly_planned'] as $weekValue)
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-600 text-right">
                                                        <span class="s-curve-point">{{ $weekValue > 0 ? number_format($weekValue, 2) : '-' }}</span>
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
                                            <td class="px-6 py-3 text-left text-xs text-blue-600 uppercase tracking-wider sticky left-0 bg-gray-100 z-30" colspan="2">
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
                                            <td class="px-6 py-3 text-left text-xs text-green-600 uppercase tracking-wider sticky left-0 bg-gray-100 z-30" colspan="2">
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
                                        {{-- --- NEW: Percentage S-Curve Chart (Inside Table) --- --}}
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script>
            async function downloadSchedulePDF() {
                const { jsPDF } = window.jspdf;
                const exportContainer = document.getElementById('pdf-export-container');
                const header = document.getElementById('pdf-header');
                const tableContainer = document.getElementById('scheduleTableContainer');
                const scrollableDiv = tableContainer.querySelector('.overflow-x-auto');
                const button = document.querySelector('button[onclick="downloadSchedulePDF()"]');
                
                // 1. Prepare for Capture
                button.disabled = true;
                button.innerHTML = 'Generating...';
                
                // Save original styles
                const originalOverflow = scrollableDiv.style.overflow;
                const originalWidth = scrollableDiv.style.width;
                
                // Reveal Header & Style Container
                header.classList.remove('hidden');
                exportContainer.style.backgroundColor = 'white';
                exportContainer.style.padding = '20px';
                
                // Force full expansion of table
                scrollableDiv.style.overflow = 'visible';
                scrollableDiv.style.width = 'fit-content';
                
                // Force container to expand to fit the table
                exportContainer.style.width = 'fit-content';
                exportContainer.style.minWidth = scrollableDiv.scrollWidth + 'px'; // Ensure it grows
                
                // Trigger chart resize to match new full width
                initOverlayChart();
                await new Promise(resolve => setTimeout(resolve, 500)); // Wait for render

                // 2. Capture
                try {
                    const canvas = await html2canvas(exportContainer, {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        windowWidth: exportContainer.scrollWidth, 
                        windowHeight: exportContainer.scrollHeight
                    });

                    // 3. Generate PDF
                    const imgData = canvas.toDataURL('image/png');
                    const pdf = new jsPDF('l', 'mm', 'a3'); 
                    
                    const pageWidth = 420;
                    const pageHeight = 297;
                    const margin = 10;
                    
                    // Calculate image size to fit within margins
                    const maxImgWidth = pageWidth - (margin * 2);
                    const maxImgHeight = pageHeight - (margin * 2);
                    
                    let imgWidth = maxImgWidth;
                    let imgHeight = canvas.height * imgWidth / canvas.width;
                    
                    // If height is too big, scale by height instead
                    if (imgHeight > maxImgHeight) {
                        imgHeight = maxImgHeight;
                        imgWidth = canvas.width * imgHeight / canvas.height;
                    }

                    // No need to add text manually, it's in the image now!
                    pdf.addImage(imgData, 'PNG', margin, margin, imgWidth, imgHeight);
                    pdf.save('Schedule_{{ $project->project_code }}.pdf');

                } catch (error) {
                    console.error('PDF Generation Error:', error);
                    alert('Failed to generate PDF. Please try again.');
                } finally {
                    // 4. Restore
                    header.classList.add('hidden');
                    exportContainer.style.backgroundColor = '';
                    exportContainer.style.padding = '';
                    exportContainer.style.width = ''; // Restore
                    exportContainer.style.minWidth = ''; // Restore
                    
                    scrollableDiv.style.overflow = originalOverflow;
                    scrollableDiv.style.width = originalWidth;
                    initOverlayChart(); // Restore chart to original size
                    
                    button.disabled = false;
                    button.innerHTML = `
                        <svg class="fill-current w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/></svg>
                        <span>Download Schedule</span>
                    `;
                }
            }

            // We need to wait for the DOM to be ready to render the chart
            let mainSCurveChart = null;
            let chartData = null;

            // Global function for Alpine to call
            window.updateChartMode = function(mode) {
                if (!mainSCurveChart || !chartData) return;

                if (mode === 'percent') {
                    // Reconstruct datasets for Percentage Mode (No Actual Cost)
                    mainSCurveChart.data.datasets = [
                        {
                            label: 'Planned Progress (%)',
                            data: chartData.planned_percent,
                            borderColor: 'rgb(59, 130, 246)', // blue
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: false,
                            tension: 0.3,
                            pointRadius: 2
                        },
                        {
                            label: 'Actual Progress (%)',
                            data: chartData.earned_percent,
                            borderColor: 'rgb(22, 163, 74)', // green
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            fill: false,
                            tension: 0.3,
                            pointRadius: 2
                        }
                    ];

                    // Update Scales
                    mainSCurveChart.options.scales.y.title.text = 'Cumulative Progress (%)';
                    mainSCurveChart.options.scales.y.ticks.callback = function(value) {
                        return value + '%';
                    };
                    
                    // Update Tooltips
                    mainSCurveChart.options.plugins.tooltip.callbacks.label = function(context) {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.parsed.y !== null) label += context.parsed.y.toFixed(2) + '%';
                        return label;
                    };

                } else {
                    // Reconstruct datasets for Cost Mode (With Actual Cost)
                    mainSCurveChart.data.datasets = [
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
                    ];

                    // Update Scales
                    mainSCurveChart.options.scales.y.title.text = 'Cumulative Cost (Rp)';
                    mainSCurveChart.options.scales.y.ticks.callback = function(value) {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
                    };

                    // Update Tooltips
                    mainSCurveChart.options.plugins.tooltip.callbacks.label = function(context) {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.parsed.y !== null) label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                        return label;
                    };
                }

                mainSCurveChart.update();
            };

            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('sCurveChart').getContext('2d');
                
                // Get data from PHP
                chartData = @json($reportData['chart_data']);

                // Initialize Chart in 'percent' mode (Default)
                mainSCurveChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: 'Planned Progress (%)',
                                data: chartData.planned_percent,
                                borderColor: 'rgb(59, 130, 246)', // blue
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: false,
                                tension: 0.3,
                                pointRadius: 2
                            },
                            {
                                label: 'Actual Progress (%)',
                                data: chartData.earned_percent,
                                borderColor: 'rgb(22, 163, 74)', // green
                                backgroundColor: 'rgba(22, 163, 74, 0.1)',
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
                                text: 'Project Performance S-Curve'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed.y !== null) label += context.parsed.y.toFixed(2) + '%';
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                title: {
                                    display: true,
                                    text: 'Cumulative Progress (%)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
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

            // --- Overlay S-Curve Logic ---
            let overlayChart = null;

            function initOverlayChart() {
                const container = document.getElementById('scheduleTableContainer');
                const table = document.getElementById('scheduleTable');
                const canvas = document.getElementById('overlaySCurve');
                
                if (!container || !table || !canvas) return;

                // 1. Calculate Dimensions & Column Centers
                const stickyCols = table.querySelectorAll('th.sticky');
                let stickyWidth = 0;
                stickyCols.forEach(col => {
                    stickyWidth += col.offsetWidth;
                });

                // Get all week columns (non-sticky headers in the first row)
                // We assume the first row of thead contains the week headers starting after sticky ones
                const headerRow = table.querySelector('thead tr');
                const allThs = Array.from(headerRow.children);
                // Filter out sticky columns to get just the week columns
                const weekCols = allThs.filter(th => !th.classList.contains('sticky'));

                // Calculate total chart width based on week columns
                let chartWidth = 0;
                weekCols.forEach(col => {
                    chartWidth += col.offsetWidth;
                }); 
                const tableHeight = table.offsetHeight;

                // 2. Position & Size Canvas
                const rows = table.querySelectorAll('tbody tr');
                let footerHeight = 0;
                // We have 2 footer rows now
                if (rows.length >= 2) {
                    const lastRow = rows[rows.length - 1];
                    const secondLastRow = rows[rows.length - 2];
                    if (lastRow) footerHeight += lastRow.offsetHeight;
                    if (secondLastRow) footerHeight += secondLastRow.offsetHeight;
                }

                // Calculate header height
                const header = table.querySelector('thead');
                const headerHeight = header ? header.offsetHeight : 50;

                canvas.width = chartWidth;
                canvas.height = tableHeight;
                canvas.style.width = chartWidth + 'px';
                canvas.style.height = tableHeight + 'px';
                canvas.style.left = stickyWidth + 'px';
                canvas.style.top = '0px';

                // 3. Prepare Data with Exact X Coordinates
                const plannedRaw = @json($reportData['footer_planned_percent']);
                const actualRaw = @json($reportData['footer_actual_percent']);
                
                // Find the spans in the first row of the body to determine X positions
                const pointSpans = table.querySelectorAll('tbody tr:first-child .s-curve-point');
                
                // Calculate X coordinates relative to the canvas
                // Canvas is absolute at left:0 in the container, but we set style.left = stickyWidth
                // So x=0 in canvas is at stickyWidth in container.
                // We need to map span center to canvas coordinate.
                
                const containerRect = container.getBoundingClientRect();
                // The canvas is positioned at 'stickyWidth' relative to the container's scrollable area?
                // Actually, the canvas is inside the scrollable div.
                // Let's use the canvas bounding rect to be sure.
                const canvasRect = canvas.getBoundingClientRect();

                const xCoordinates = [];
                pointSpans.forEach(span => {
                    const spanRect = span.getBoundingClientRect();
                    // Center of the span relative to the canvas
                    const centerX = (spanRect.left + spanRect.width / 2) - canvasRect.left;
                    xCoordinates.push(centerX);
                });

                // Map to {x, y} format
                // We assume the spans correspond 1:1 to the data points (weeks)
                const plannedPoints = plannedRaw.map((val, index) => {
                    if (index >= xCoordinates.length) return null;
                    return {
                        x: xCoordinates[index],
                        y: val
                    };
                }).filter(p => p !== null);

                const actualPoints = actualRaw.map((val, index) => {
                    if (index >= xCoordinates.length) return null;
                    return {
                        x: xCoordinates[index],
                        y: val
                    };
                }).filter(p => p !== null);

                // 4. Draw Chart
                const ctx = canvas.getContext('2d');

                if (overlayChart) overlayChart.destroy();

                overlayChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        datasets: [
                            {
                                label: 'Planned',
                                data: plannedPoints,
                                borderColor: 'rgba(59, 130, 246, 0.8)', // Blue
                                borderWidth: 3,
                                pointRadius: 4,
                                pointBackgroundColor: 'white',
                                pointBorderColor: 'rgb(59, 130, 246)',
                                pointBorderWidth: 2,
                                tension: 0.4,
                                fill: false
                            },
                            {
                                label: 'Actual',
                                data: actualPoints,
                                borderColor: 'rgba(22, 163, 74, 0.8)', // Green
                                borderWidth: 3,
                                pointRadius: 4,
                                pointBackgroundColor: 'white',
                                pointBorderColor: 'rgb(22, 163, 74)',
                                pointBorderWidth: 2,
                                tension: 0.4,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: false,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                left: 0, // No padding, we use exact coordinates
                                right: 0,
                                top: headerHeight + 10,
                                bottom: footerHeight + 10
                            }
                        },
                        scales: {
                            x: {
                                type: 'linear', // Use linear scale for pixel coordinates
                                display: false, 
                                min: 0,
                                max: chartWidth, // Match canvas width exactly
                                offset: false
                            },
                            y: {
                                display: false,
                                min: 0,
                                max: 100
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        animation: false
                    }
                });
            }

            // Initialize on load and resize
            window.addEventListener('load', initOverlayChart);
            window.addEventListener('resize', initOverlayChart);
            
        </script>
    @endpush

</x-app-layout>