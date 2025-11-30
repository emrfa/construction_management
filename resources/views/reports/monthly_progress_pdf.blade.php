<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Monthly Progress Report</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        @page {
            margin: 1cm;
            size: A4 landscape;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 10px;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header-left {
            float: left;
            width: 50%;
        }
        .header-right {
            float: right;
            width: 50%;
            text-align: right;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
        }
        .project-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #d97706; /* Orange-600 */
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #f3f4f6; /* Gray-100 */
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .bg-gray-200 { background-color: #e5e7eb; }
        .bg-gray-300 { background-color: #d1d5db; }
        
        /* Page break handling */
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="company-name">MK & GENERAL CONTRACTOR</div>
            <div>PT Sketsaa Artama Indonesia</div>
            <div>Jl. Tubagus Ismail Indah no. 11 Bandung</div>
        </div>
        <div class="header-right">
            <div class="project-name">{{ $project->quotation->project_name }}</div>
            <div class="report-title">LAPORAN KEMAJUAN PROGRAM KERJA</div>
            <div>PERIODE BULAN : {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">NO</th>
                <th rowspan="2">JENIS PEKERJAAN</th>
                <th colspan="2">NILAI PEKERJAAN</th>
                <th colspan="2">PROGRES {{ \Carbon\Carbon::parse($month)->subMonth()->translatedFormat('M Y') }}</th>
                <th colspan="1">KENAIKAN</th>
                <th colspan="3">PROGRES {{ \Carbon\Carbon::parse($month)->translatedFormat('M Y') }}</th>
            </tr>
            <tr>
                <th style="width: 50px;">BOBOT</th>
                <th style="width: 80px;">Rp (JUTA)</th>
                <th style="width: 50px;">BOBOT</th>
                <th style="width: 50px;">PROGRESS</th>
                <th style="width: 50px;">PROGRESS</th>
                <th style="width: 50px;">BOBOT</th>
                <th style="width: 50px;">PROGRESS</th>
                <th style="width: 80px;">Rp (JUTA)</th>
            </tr>
        </thead>
        <tbody>
            <!-- I. KONTRAK PEKERJAAN FISIK -->
            <tr class="bg-gray-200 font-bold">
                <td class="text-center">I</td>
                <td colspan="9">KONTRAK PEKERJAAN FISIK</td>
            </tr>
            @foreach ($reportDataOriginal as $index => $item)
                @include('reports.partials.monthly_row_pdf', ['item' => $item, 'level' => 0, 'number' => $loop->iteration])
            @endforeach
            
            <!-- Subtotal Original -->
            <tr class="font-bold">
                <td colspan="2" class="text-right">SUBTOTAL I</td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->sum('weight'), 3) }}%</td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->sum('contract_value') / 1000000, 3) }}</td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->sum('previous_bobot'), 3) }}%</td>
                <td class="text-right">
                    @if(collect($reportDataOriginal)->sum('weight') > 0)
                        {{ number_format((collect($reportDataOriginal)->sum('previous_bobot') / collect($reportDataOriginal)->sum('weight')) * 100, 2) }}%
                    @else
                        0.00%
                    @endif
                </td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->sum('increase_bobot'), 3) }}%</td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->sum('current_bobot'), 3) }}%</td>
                <td class="text-right">
                    @if(collect($reportDataOriginal)->sum('weight') > 0)
                        {{ number_format((collect($reportDataOriginal)->sum('current_bobot') / collect($reportDataOriginal)->sum('weight')) * 100, 2) }}%
                    @else
                        0.00%
                    @endif
                </td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->sum('current_value_rp') / 1000000, 3) }}</td>
            </tr>

            <!-- II. PEKERJAAN TAMBAH -->
            @if(count($reportDataAdditional) > 0)
                <tr class="bg-gray-200 font-bold">
                    <td class="text-center">II</td>
                    <td colspan="9">PEKERJAAN TAMBAH</td>
                </tr>
                @foreach ($reportDataAdditional as $index => $item)
                    @include('reports.partials.monthly_row_pdf', ['item' => $item, 'level' => 0, 'number' => $loop->iteration])
                @endforeach

                <!-- Subtotal Additional -->
                <tr class="font-bold">
                    <td colspan="2" class="text-right">SUBTOTAL II</td>
                    <td class="text-right">{{ number_format(collect($reportDataAdditional)->sum('weight'), 3) }}%</td>
                    <td class="text-right">{{ number_format(collect($reportDataAdditional)->sum('contract_value') / 1000000, 3) }}</td>
                    <td class="text-right">{{ number_format(collect($reportDataAdditional)->sum('previous_bobot'), 3) }}%</td>
                    <td class="text-right">
                        @if(collect($reportDataAdditional)->sum('weight') > 0)
                            {{ number_format((collect($reportDataAdditional)->sum('previous_bobot') / collect($reportDataAdditional)->sum('weight')) * 100, 2) }}%
                        @else
                            0.00%
                        @endif
                    </td>
                    <td class="text-right">{{ number_format(collect($reportDataAdditional)->sum('increase_bobot'), 3) }}%</td>
                    <td class="text-right">{{ number_format(collect($reportDataAdditional)->sum('current_bobot'), 3) }}%</td>
                    <td class="text-right">
                        @if(collect($reportDataAdditional)->sum('weight') > 0)
                            {{ number_format((collect($reportDataAdditional)->sum('current_bobot') / collect($reportDataAdditional)->sum('weight')) * 100, 2) }}%
                        @else
                            0.00%
                        @endif
                    </td>
                    <td class="text-right">{{ number_format(collect($reportDataAdditional)->sum('current_value_rp') / 1000000, 3) }}</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="bg-gray-300 font-bold">
                <td colspan="2" class="text-center">PROGRESS KESELURUHAN (I + II)</td>
                <td class="text-right">100.000%</td>
                <td class="text-right">{{ number_format($grandTotalContract / 1000000, 3) }}</td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('previous_bobot'), 3) }}%</td>
                <td class="text-right">
                    {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('previous_bobot'), 2) }}%
                </td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('increase_bobot'), 3) }}%</td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('current_bobot'), 3) }}%</td>
                <td class="text-right">
                    {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('current_bobot'), 2) }}%
                </td>
                <td class="text-right">{{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('current_value_rp') / 1000000, 3) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
