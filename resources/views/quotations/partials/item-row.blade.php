@php
    $isParent = $item->children->isNotEmpty();
    // Base padding (px-6 = 24px) + Indent (20px per level)
    $paddingLeft = (24 + ($level * 20)) . 'px';
    
    // Determine row background based on type/level
    $rowClass = 'bg-white';
    if ($level === 0 && $isParent) {
        $rowClass = 'bg-gray-100'; // Sub-Project (Neutral)
    } elseif ($level === 1 && $isParent) {
        $rowClass = 'bg-gray-50'; // Work Type
    }
@endphp

<tr class="hover:bg-gray-50 transition-colors {{ $rowClass }}">
    
    {{-- Description --}}
    <td class="py-3 whitespace-nowrap text-sm font-medium text-gray-900 pr-6" style="padding-left: {{ $paddingLeft }};">
        <div class="flex items-center">
            <span class="truncate @if($level === 0 && $isParent) font-bold text-gray-900 @elseif($isParent) font-semibold text-gray-800 @else text-gray-600 @endif">
                {{ $item->description }}
            </span>
        </div>
    </td>

    {{-- Code --}}
    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-400 italic">
        @if(!$isParent)
            {{ $item->item_code }}
        @endif
    </td>

    {{-- Unit --}}
    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 text-center">
        @if(!$isParent)
            {{ $item->uom }}
        @endif
    </td>

    {{-- Quantity --}}
    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
        @if(!$isParent)
            {{ (float)$item->quantity }}
        @endif
    </td>

    {{-- Unit Price --}}
    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
        @if(!$isParent)
            {{ number_format($item->unit_price, 0, ',', '.') }}
        @endif
    </td>

    {{-- Subtotal --}}
    <td class="px-6 py-3 whitespace-nowrap text-sm text-right font-medium @if($isParent) text-gray-900 @else text-gray-600 @endif">
        {{ number_format($item->subtotal, 0, ',', '.') }}
    </td>
</tr>

@if ($isParent)
    @foreach ($item->children as $child)
        @include('quotations.partials.item-row', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif