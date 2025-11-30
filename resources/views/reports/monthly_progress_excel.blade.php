    <!-- Header -->
    <tr>
        <th colspan="10" style="font-weight: bold; font-size: 16px; text-align: center;">{{ $project->quotation->project_name }}</th>
    </tr>
    <tr>
        <th colspan="10" style="font-weight: bold; font-size: 14px; text-align: center; color: #d97706;">LAPORAN KEMAJUAN PROGRAM KERJA</th>
    </tr>
    <tr>
        <th colspan="10" style="text-align: center;">PERIODE BULAN : {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</th>
    </tr>
    <tr><td colspan="10">&nbsp;</td></tr>
    <tr>
        <th rowspan="2" style="width: 5px; text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">NO</th>
        <th rowspan="2" style="width: 40px; text-align: left; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">JENIS PEKERJAAN</th>
        <th colspan="2" style="text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">NILAI PEKERJAAN</th>
        <th colspan="2" style="text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">PROGRES {{ \Carbon\Carbon::parse($month)->subMonth()->translatedFormat('M Y') }}</th>
        <th colspan="1" style="text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">KENAIKAN</th>
        <th colspan="3" style="text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">PROGRES {{ \Carbon\Carbon::parse($month)->translatedFormat('M Y') }}</th>
    </tr>
    <tr>
        <th style="width: 10px; text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">BOBOT</th>
        <th style="width: 15px; text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">Rp (JUTA)</th>
        <th style="width: 10px; text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">BOBOT</th>
        <th style="width: 10px; text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">PROGRESS</th>
        <th style="width: 10px; text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">PROGRESS</th>
        <th style="width: 10px; text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">BOBOT</th>
        <th style="width: 10px; text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">PROGRESS</th>
        <th style="width: 15px; text-align: center; font-weight: bold; background-color: #f3f4f6; border: 1px solid #000000;">Rp (JUTA)</th>
    </tr>

    <!-- I. KONTRAK PEKERJAAN FISIK -->
    <tr>
        <td style="text-align: center; font-weight: bold; background-color: #e5e7eb; border: 1px solid #000000;">I</td>
        <td colspan="10" style="font-weight: bold; background-color: #e5e7eb; border: 1px solid #000000;">KONTRAK PEKERJAAN FISIK</td>
    </tr>
        @foreach ($reportDataOriginal as $index => $item)
            @include('reports.partials.monthly_row_excel', ['item' => $item, 'level' => 0, 'number' => $loop->iteration])
        @endforeach
        
    <!-- Subtotal Original -->
    <tr>
        <td colspan="2" style="text-align: right; font-weight: bold; border: 1px solid #000000;">SUBTOTAL I</td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->sum('weight'), 3) }}%</td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->sum('contract_value') / 1000000, 3) }}</td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->sum('previous_bobot'), 3) }}%</td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">
                @if(collect($reportDataOriginal)->sum('weight') > 0)
                    {{ number_format((collect($reportDataOriginal)->sum('previous_bobot') / collect($reportDataOriginal)->sum('weight')) * 100, 2) }}%
                @else
                    0.00%
                @endif
            </td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->sum('increase_bobot'), 3) }}%</td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->sum('current_bobot'), 3) }}%</td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">
                @if(collect($reportDataOriginal)->sum('weight') > 0)
                    {{ number_format((collect($reportDataOriginal)->sum('current_bobot') / collect($reportDataOriginal)->sum('weight')) * 100, 2) }}%
                @else
                    0.00%
                @endif
            </td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->sum('current_value_rp') / 1000000, 3) }}</td>
        </tr>

    <!-- II. PEKERJAAN TAMBAH -->
    @if(count($reportDataAdditional) > 0)
        <tr>
            <td style="text-align: center; font-weight: bold; background-color: #e5e7eb; border: 1px solid #000000;">II</td>
            <td colspan="10" style="font-weight: bold; background-color: #e5e7eb; border: 1px solid #000000;">PEKERJAAN TAMBAH</td>
        </tr>
            @foreach ($reportDataAdditional as $index => $item)
                @include('reports.partials.monthly_row_excel', ['item' => $item, 'level' => 0, 'number' => $loop->iteration])
            @endforeach

        <!-- Subtotal Additional -->
        <tr>
            <td colspan="2" style="text-align: right; font-weight: bold; border: 1px solid #000000;">SUBTOTAL II</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataAdditional)->sum('weight'), 3) }}%</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataAdditional)->sum('contract_value') / 1000000, 3) }}</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataAdditional)->sum('previous_bobot'), 3) }}%</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">
                    @if(collect($reportDataAdditional)->sum('weight') > 0)
                        {{ number_format((collect($reportDataAdditional)->sum('previous_bobot') / collect($reportDataAdditional)->sum('weight')) * 100, 2) }}%
                    @else
                        0.00%
                    @endif
                </td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataAdditional)->sum('increase_bobot'), 3) }}%</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataAdditional)->sum('current_bobot'), 3) }}%</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">
                    @if(collect($reportDataAdditional)->sum('weight') > 0)
                        {{ number_format((collect($reportDataAdditional)->sum('current_bobot') / collect($reportDataAdditional)->sum('weight')) * 100, 2) }}%
                    @else
                        0.00%
                    @endif
                </td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">{{ number_format(collect($reportDataAdditional)->sum('current_value_rp') / 1000000, 3) }}</td>
            </tr>
        @endif


    <!-- Footer -->
    <tr>
        <td colspan="2" style="text-align: center; font-weight: bold; background-color: #d1d5db; border: 1px solid #000000;">PROGRESS KESELURUHAN (I + II)</td>
        <td style="text-align: right; font-weight: bold; background-color: #d1d5db; border: 1px solid #000000;">100.000%</td>
        <td style="text-align: right; font-weight: bold; background-color: #d1d5db; border: 1px solid #000000;">{{ number_format($grandTotalContract / 1000000, 3) }}</td>
        <td style="text-align: right; font-weight: bold; background-color: #d1d5db; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('previous_bobot'), 3) }}%</td>
        <td style="text-align: right; font-weight: bold; background-color: #d1d5db; border: 1px solid #000000;">
            {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('previous_bobot'), 2) }}%
        </td>
        <td style="text-align: right; font-weight: bold; background-color: #d1d5db; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('increase_bobot'), 3) }}%</td>
        <td style="text-align: right; font-weight: bold; background-color: #d1d5db; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('current_bobot'), 3) }}%</td>
        <td style="text-align: right; font-weight: bold; background-color: #d1d5db; border: 1px solid #000000;">
            {{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('current_bobot'), 2) }}%
        </td>
        <td style="text-align: right; font-weight: bold; background-color: #d1d5db; border: 1px solid #000000;">{{ number_format(collect($reportDataOriginal)->merge($reportDataAdditional)->sum('current_value_rp') / 1000000, 3) }}</td>
    </tr>
