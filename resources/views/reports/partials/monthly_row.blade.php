@props(['item', 'level', 'number'])

<tr class="{{ $level == 0 ? 'font-bold bg-gray-50' : '' }}">
    <td class="border border-gray-300 px-2 py-1 text-center align-top">
        {{ $level == 0 ? $number : '' }}
    </td>
    <td class="border border-gray-300 px-2 py-1 align-top" style="padding-left: {{ $level * 20 + 8 }}px">
        {{ $item['description'] }}
    </td>
    
    <!-- Contract -->
    <td class="border border-gray-300 px-2 py-1 text-right align-top">
        {{ number_format($item['weight'], 3) }}%
    </td>
    <td class="border border-gray-300 px-2 py-1 text-right align-top">
        {{ number_format($item['contract_value'] / 1000000, 3) }}
    </td>

    <!-- Previous -->
    <td class="border border-gray-300 px-2 py-1 text-right align-top text-gray-600">
        {{ number_format($item['previous_bobot'], 3) }}%
    </td>
    <td class="border border-gray-300 px-2 py-1 text-right align-top text-gray-600">
        {{ number_format($item['previous_progress_percent'], 2) }}%
    </td>

    <!-- Increase -->
    <td class="border border-gray-300 px-2 py-1 text-right align-top text-blue-600">
        {{ number_format($item['increase_percent'], 3) }}%
    </td>

    <!-- Current -->
    <td class="border border-gray-300 px-2 py-1 text-right align-top font-semibold">
        {{ number_format($item['current_bobot'], 3) }}%
    </td>
    <td class="border border-gray-300 px-2 py-1 text-right align-top font-semibold">
        {{ number_format($item['current_progress_percent'], 2) }}%
    </td>
    <td class="border border-gray-300 px-2 py-1 text-right align-top font-semibold">
        {{ number_format($item['current_value_rp'] / 1000000, 3) }}
    </td>
</tr>

@if (!empty($item['children']))
    @foreach ($item['children'] as $child)
        @include('reports.partials.monthly_row', ['item' => $child, 'level' => $level + 1, 'number' => ''])
    @endforeach
@endif
