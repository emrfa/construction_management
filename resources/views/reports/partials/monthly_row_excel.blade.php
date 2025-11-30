@props(['item', 'level', 'number'])

<tr>
    <td style="text-align: center; vertical-align: top; border: 1px solid #000000;">
        {{ $level == 0 ? $number : '' }}
    </td>
    <td style="padding-left: {{ $level * 2 }}px; vertical-align: top; border: 1px solid #000000; {{ $level == 0 ? 'font-weight: bold;' : '' }}">
        {{ $item['description'] }}
    </td>
    
    <td style="text-align: right; vertical-align: top; border: 1px solid #000000;">
        {{ number_format($item['weight'], 3) }}%
    </td>
    <td style="text-align: right; vertical-align: top; border: 1px solid #000000;">
        {{ number_format($item['contract_value'] / 1000000, 3) }}
    </td>

    <td style="text-align: right; vertical-align: top; border: 1px solid #000000; color: #4b5563;">
        {{ number_format($item['previous_bobot'], 3) }}%
    </td>
    <td style="text-align: right; vertical-align: top; border: 1px solid #000000; color: #4b5563;">
        {{ number_format($item['previous_progress_percent'], 2) }}%
    </td>

    <td style="text-align: right; vertical-align: top; border: 1px solid #000000; color: #2563eb;">
        {{ number_format($item['increase_percent'], 3) }}%
    </td>

    <td style="text-align: right; vertical-align: top; border: 1px solid #000000; font-weight: bold;">
        {{ number_format($item['current_bobot'], 3) }}%
    </td>
    <td style="text-align: right; vertical-align: top; border: 1px solid #000000; font-weight: bold;">
        {{ number_format($item['current_progress_percent'], 2) }}%
    </td>
    <td style="text-align: right; vertical-align: top; border: 1px solid #000000; font-weight: bold;">
        {{ number_format($item['current_value_rp'] / 1000000, 3) }}
    </td>
</tr>

@if (!empty($item['children']))
    @foreach ($item['children'] as $child)
        @include('reports.partials.monthly_row_excel', ['item' => $child, 'level' => $level + 1, 'number' => ''])
    @endforeach
@endif
