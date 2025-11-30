@props(['item', 'level', 'number'])

<tr class="{{ $level == 0 ? 'font-bold' : '' }}">
    <td class="text-center" style="vertical-align: top;">
        {{ $level == 0 ? $number : '' }}
    </td>
    <td style="padding-left: {{ $level * 15 + 4 }}px; vertical-align: top; word-wrap: break-word;">
        {{ $item['description'] }}
    </td>
    
    <!-- Contract -->
    <td class="text-right" style="vertical-align: top;">
        {{ number_format($item['weight'], 3) }}%
    </td>
    <td class="text-right" style="vertical-align: top;">
        {{ number_format($item['contract_value'] / 1000000, 3) }}
    </td>

    <!-- Previous -->
    <td class="text-right" style="vertical-align: top; color: #4b5563;">
        {{ number_format($item['previous_bobot'], 3) }}%
    </td>
    <td class="text-right" style="vertical-align: top; color: #4b5563;">
        {{ number_format($item['previous_progress_percent'], 2) }}%
    </td>

    <!-- Increase -->
    <td class="text-right" style="vertical-align: top; color: #2563eb;">
        {{ number_format($item['increase_percent'], 3) }}%
    </td>

    <!-- Current -->
    <td class="text-right" style="vertical-align: top; font-weight: bold;">
        {{ number_format($item['current_bobot'], 3) }}%
    </td>
    <td class="text-right" style="vertical-align: top; font-weight: bold;">
        {{ number_format($item['current_progress_percent'], 2) }}%
    </td>
    <td class="text-right" style="vertical-align: top; font-weight: bold;">
        {{ number_format($item['current_value_rp'] / 1000000, 3) }}
    </td>
</tr>

@if (!empty($item['children']))
    @foreach ($item['children'] as $child)
        @include('reports.partials.monthly_row_pdf', ['item' => $child, 'level' => $level + 1, 'number' => ''])
    @endforeach
@endif
