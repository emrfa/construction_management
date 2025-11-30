@php
    $isParent = $item->children->isNotEmpty();
    $padding = $level * 20 . 'px'; // 20px indent per level
    $progress = $item->latest_progress;
    $itemActualCost = $item->actual_cost;
    $itemBudgetLeft = $item->budget_left;
@endphp

<div class="grid grid-cols-12 gap-1 items-center py-1 border-b @if($isParent) bg-gray-50 @else hover:bg-blue-50/30 @endif text-xs transition-colors">

    {{-- Description --}}
    <div class="col-span-3" style="padding-left: {{ $padding }};">
        <span class="@if($isParent) font-bold @endif">{{ $item->description }}</span>
    </div>

    {{-- Code --}}
    <div class="col-span-1">
        @if(!$isParent)
            <button type="button" 
                    onclick="openTaskDrillDown({{ $item->id }})"
                    class="text-indigo-600 hover:text-indigo-900 hover:underline font-medium cursor-pointer text-left"
                    title="Click to see details">
                {{ $item->item_code ?? 'View →' }}
            </button>
        @else
            {{ $item->item_code }}
        @endif
    </div>

    {{-- UOM --}}

    <div class="col-span-1">{{ $item->uom }}</div>

    {{-- Original Qty --}}
    <div class="col-span-1 text-right text-gray-500">
        @if(!$isParent) {{ number_format($item->original_quantity ?? $item->quantity, 2, ',', '.') }} @endif
    </div>

    {{-- Revised Qty --}}
    <div class="col-span-1 text-right font-medium">
        @if(!$isParent) {{ number_format($item->quantity, 2, ',', '.') }} @endif
    </div>

    {{-- Original Budget --}}
    <div class="col-span-1 text-right text-gray-500">
        {{ number_format($item->original_subtotal ?? $item->subtotal, 0, ',', '.') }}
    </div>

    {{-- Revised Budget --}}
    <div class="col-span-1 text-right font-semibold">
        {{ number_format($item->subtotal, 0, ',', '.') }}
    </div>

    {{-- Actual Cost --}}
    <div class="col-span-1 text-right text-orange-600 font-semibold">
        {{ number_format($itemActualCost, 0, ',', '.') }}
    </div>

    {{-- Budget Left --}}
    <div class="col-span-1 text-right font-semibold {{ $itemBudgetLeft >= 0 ? 'text-green-600' : 'text-red-600' }}">
        {{ number_format($itemBudgetLeft, 0, ',', '.') }}
    </div>

    {{-- Progress --}}
    <div class="col-span-1 text-center font-medium">
       @if ($isParent)
        {{-- Display parent progress (calculated by the accessor) without a link --}}
        <span class="
            @if($progress == 100) text-green-600
            @elseif($progress > 0) text-blue-600
            @else text-gray-500
            @endif">
            {{-- Format with one decimal place for potentially fractional averages --}}
            {{ number_format($progress, 1) }}%
        </span>
    @else
        {{-- Display leaf node progress with link (keep original formatting or adjust) --}}
        <a href="{{ route('progress.history', $item) }}"
           class="hover:underline
            @if($progress == 100) text-green-600
            @elseif($progress > 0) text-blue-600
            @else text-gray-500
            @endif">
            {{-- Format without decimal place for direct input values --}}
            {{ number_format($progress, 0) }}%
        </a>
    @endif
    </div>
</div>

@if ($isParent)
    @foreach ($item->children as $child)
        @include('projects.partials.item-row', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif