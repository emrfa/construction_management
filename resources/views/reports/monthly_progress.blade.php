<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Monthly Progress Report') }}
            </h2>
            <div class="flex items-center space-x-4">
                <form method="GET" action="{{ route('reports.monthly_progress', $project) }}" class="flex items-center space-x-2">
                    <label for="month" class="text-sm font-medium text-gray-700">Period:</label>
                    <input type="month" name="month" id="month" value="{{ $month }}" 
                           class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           onchange="this.form.submit()">
                </form>

                <!-- Export Buttons -->
                <div class="flex space-x-2">
                    <a href="{{ route('reports.monthly_progress.export', ['project' => $project, 'month' => $month, 'type' => 'pdf']) }}" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        PDF
                    </a>
                    <a href="{{ route('reports.monthly_progress.export', ['project' => $project, 'month' => $month, 'type' => 'excel']) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Excel
                    </a>
                </div>

                <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ __('Back to Project') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Report Header -->
                    <div class="mb-8 text-center border-b pb-4">
                        <div class="flex justify-between items-start mb-4">
                            <div class="text-left">
                                <h1 class="text-lg font-bold">MK & GENERAL CONTRACTOR</h1>
                                <div class="text-sm text-gray-600">PT Sketsaa Artama Indonesia</div>
                                <div class="text-xs text-gray-500">Jl. Tubagus Ismail Indah no. 11 Bandung</div>
                            </div>
                            <div class="text-right">
                                <h2 class="text-xl font-bold uppercase">{{ $project->quotation->project_name }}</h2>
                                <h3 class="text-lg font-semibold text-orange-600 uppercase">LAPORAN KEMAJUAN PROGRAM KERJA</h3>
                                <p class="font-medium">PERIODE BULAN : {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Report Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-300 text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th rowspan="2" class="border border-gray-300 px-2 py-1 text-center w-10">NO</th>
                                    <th rowspan="2" class="border border-gray-300 px-2 py-1 text-left">JENIS PEKERJAAN</th>
                                    <th colspan="2" class="border border-gray-300 px-2 py-1 text-center">NILAI PEKERJAAN</th>
                                    <th colspan="2" class="border border-gray-300 px-2 py-1 text-center">PROGRES {{ \Carbon\Carbon::parse($month)->subMonth()->translatedFormat('M Y') }}</th>
                                    <th colspan="1" class="border border-gray-300 px-2 py-1 text-center">KENAIKAN</th>
                                    <th colspan="3" class="border border-gray-300 px-2 py-1 text-center">PROGRES {{ \Carbon\Carbon::parse($month)->translatedFormat('M Y') }}</th>
                                </tr>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 px-2 py-1 text-center w-20">BOBOT</th>
                                    <th class="border border-gray-300 px-2 py-1 text-center w-32">Rp (JUTA)</th>
                                    <th class="border border-gray-300 px-2 py-1 text-center w-20">BOBOT</th>
                                    <th class="border border-gray-300 px-2 py-1 text-center w-20">PROGRESS</th>
                                    <th class="border border-gray-300 px-2 py-1 text-center w-20">PROGRESS</th>
                                    <th class="border border-gray-300 px-2 py-1 text-center w-20">BOBOT</th>
                                    <th class="border border-gray-300 px-2 py-1 text-center w-20">PROGRESS</th>
                                    <th class="border border-gray-300 px-2 py-1 text-center w-32">Rp (JUTA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- I. KONTRAK PEKERJAAN FISIK -->
                                <tr class="bg-gray-200 font-bold">
                                    <td class="border border-gray-300 px-2 py-1 text-center">I</td>
                                    <td class="border border-gray-300 px-2 py-1" colspan="10">KONTRAK PEKERJAAN FISIK</td>
                                </tr>
                                @foreach ($reportDataOriginal as $index => $item)
                                    @include('reports.partials.monthly_row', ['item' => $item, 'level' => 0, 'number' => $loop->iteration])
                                @endforeach
                                
                                <!-- Subtotal Original -->
                                <tr class="bg-gray-100 font-bold">
                                    <td colspan="2" class="border border-gray-300 px-2 py-1 text-right">SUBTOTAL I</td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->sum('weight'), 3) }}%
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->sum('contract_value') / 1000000, 3) }}
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->sum('previous_bobot'), 3) }}%
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        @if(collect($reportDataOriginal)->sum('weight') > 0)
                                            {{ number_format((collect($reportDataOriginal)->sum('previous_bobot') / collect($reportDataOriginal)->sum('weight')) * 100, 2) }}%
                                        @else
                                            0.00%
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->sum('increase_bobot'), 3) }}%
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->sum('current_bobot'), 3) }}%
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        @if(collect($reportDataOriginal)->sum('weight') > 0)
                                            {{ number_format((collect($reportDataOriginal)->sum('current_bobot') / collect($reportDataOriginal)->sum('weight')) * 100, 2) }}%
                                        @else
                                            0.00%
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->sum('current_value_rp') / 1000000, 3) }}
                                    </td>
                                </tr>

                                <!-- II. PEKERJAAN TAMBAH -->
                                @if(count($reportDataAdditional) > 0)
                                    <tr class="bg-gray-200 font-bold">
                                        <td class="border border-gray-300 px-2 py-1 text-center">II</td>
                                        <td class="border border-gray-300 px-2 py-1" colspan="10">PEKERJAAN TAMBAH</td>
                                    </tr>
                                    @foreach ($reportDataAdditional as $index => $item)
                                        @include('reports.partials.monthly_row', ['item' => $item, 'level' => 0, 'number' => $loop->iteration])
                                    @endforeach

                                    <!-- Subtotal Additional -->
                                    <tr class="bg-gray-100 font-bold">
                                        <td colspan="2" class="border border-gray-300 px-2 py-1 text-right">SUBTOTAL II</td>
                                        <td class="border border-gray-300 px-2 py-1 text-right">
                                            {{ number_format(collect($reportDataAdditional)->sum('weight'), 3) }}%
                                        </td>
                                        <td class="border border-gray-300 px-2 py-1 text-right">
                                            {{ number_format(collect($reportDataAdditional)->sum('contract_value') / 1000000, 3) }}
                                        </td>
                                        <td class="border border-gray-300 px-2 py-1 text-right">
                                            {{ number_format(collect($reportDataAdditional)->sum('previous_bobot'), 3) }}%
                                        </td>
                                        <td class="border border-gray-300 px-2 py-1 text-right">
                                            @if(collect($reportDataAdditional)->sum('weight') > 0)
                                                {{ number_format((collect($reportDataAdditional)->sum('previous_bobot') / collect($reportDataAdditional)->sum('weight')) * 100, 2) }}%
                                            @else
                                                0.00%
                                            @endif
                                        </td>
                                        <td class="border border-gray-300 px-2 py-1 text-right">
                                            {{ number_format(collect($reportDataAdditional)->sum('increase_bobot'), 3) }}%
                                        </td>
                                        <td class="border border-gray-300 px-2 py-1 text-right">
                                            {{ number_format(collect($reportDataAdditional)->sum('current_bobot'), 3) }}%
                                        </td>
                                        <td class="border border-gray-300 px-2 py-1 text-right">
                                            @if(collect($reportDataAdditional)->sum('weight') > 0)
                                                {{ number_format((collect($reportDataAdditional)->sum('current_bobot') / collect($reportDataAdditional)->sum('weight')) * 100, 2) }}%
                                            @else
                                                0.00%
                                            @endif
                                        </td>
                                        <td class="border border-gray-300 px-2 py-1 text-right">
                                            {{ number_format(collect($reportDataAdditional)->sum('current_value_rp') / 1000000, 3) }}
                                        </td>
                                    </tr>
                                @endif

                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-300 font-bold text-base">
                                    <td colspan="2" class="border border-gray-300 px-2 py-1 text-center">PROGRESS KESELURUHAN (I + II)</td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">100.000%</td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($grandTotalContract / 1000000, 3) }}</td>
                                    
                                    <!-- Previous Totals -->
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('previous_bobot'), 3) }}%
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('previous_bobot'), 2) }}%
                                    </td>

                                    <!-- Increase -->
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('increase_bobot'), 3) }}%
                                    </td>

                                    <!-- Current Totals -->
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('current_bobot'), 3) }}%
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('current_bobot'), 2) }}%
                                    </td>
                                    <td class="border border-gray-300 px-2 py-1 text-right">
                                        {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('current_value_rp') / 1000000, 3) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
