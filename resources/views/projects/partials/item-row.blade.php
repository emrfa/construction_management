@php
    $isParent = $item->children->isNotEmpty();
    $padding = $level * 20 . 'px'; // 20px indent per level
    $progress = $item->latest_progress; 
@endphp

<div class="grid grid-cols-12 gap-2 items-center py-2 border-b @if($isParent) bg-gray-50 @endif">
    
    <div class="col-span-5" style="padding-left: {{ $padding }};">
        <span class="@if($isParent) font-bold @endif">{{ $item->description }}</span>
    </div>

    <div class="col-span-1 text-sm">{{ $item->item_code }}</div>

    <div class="col-span-1 text-sm">{{ $item->uom }}</div>

    <div class="col-span-1 text-sm text-right">
        @if(!$isParent)
            {{ $item->quantity }}
        @endif
    </div>

    <div class="col-span-2 text-sm text-right">
        @if(!$isParent)
            {{ number_format($item->unit_price, 0, ',', '.') }}
        @endif
    </div>

    <div class="col-span-1 text-sm text-right font-semibold">
        {{ number_format($item->subtotal, 0, ',', '.') }}
    </div>

    <div class="col-span-1 text-center font-medium">
        @if ($isParent)
            <span class="@if($progress > 0) text-blue-600 @else text-gray-500 @endif">
                {{ $progress }}%
            </span>
        @else
            <a href="{{ route('progress.history', $item) }}" 
            class="hover:underline
                @if($progress == 100) text-green-600
                @elseif($progress > 0) text-blue-600
                @else text-gray-500
                @endif">
                {{ $progress }}%
            </a>
        @endif
    </div>
</div>

@if ($isParent)
    @foreach ($item->children as $child)
        @include('projects.partials.item-row', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif