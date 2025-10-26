@php
    $isParent = $item->children->isNotEmpty();
    $padding = $level * 20 . 'px'; // 20px indent per level
    $progress = $item->latest_progress;
    $itemActualCost = $item->actual_cost;
    $itemBudgetLeft = $item->budget_left;
@endphp

<div class="grid grid-cols-12 gap-1 items-center py-1 border-b @if($isParent) bg-gray-50 @endif text-xs">

    {{-- Description --}}
    <div class="col-span-4" style="padding-left: {{ $padding }};">
        <span class="@if($isParent) font-bold @endif">{{ $item->description }}</span>
    </div>

    {{-- Code --}}
    <div class="col-span-1">{{ $item->item_code }}</div>

    {{-- UOM & Qty (Combined?) - Let's keep separate for now --}}
    <div class="col-span-1">{{ $item->uom }}</div>
    <div class="col-span-1 text-right">
        @if(!$isParent) {{ number_format($item->quantity, 2, ',', '.') }} @endif
    </div>

    {{-- Item Budget --}}
    <div class="col-span-1 text-right font-semibold">
        {{ number_format($item->subtotal, 0, ',', '.') }}
    </div>

    {{-- Actual Cost --}}
    <div class="col-span-2 text-right text-orange-600 font-semibold">
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