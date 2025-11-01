{{-- This is a recursive file for the project scheduler --}}
<div class="wbs-item" style="padding-left: {{ $level * 2 }}rem;">
    
    <div class="grid grid-cols-12 gap-4 items-center py-2 {{ $item->children->isNotEmpty() ? 'font-bold bg-gray-50 p-2 rounded' : 'border-b border-gray-100' }}">
        
        {{-- Item Description --}}
        <div class="col-span-6">
            {{ $item->description }}
            @if ($item->children->isEmpty())
                <span class="text-xs font-normal text-gray-500"> (Task)</span>
            @endif
        </div>

        {{-- Planned Start Date --}}
        <div class="col-span-3">
            {{-- We only allow editing dates for leaf nodes (tasks) --}}
            @if ($item->children->isEmpty())
                <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                <input type="date" name="items[{{ $item->id }}][planned_start]" 
                       value="{{ old('items.'.$item->id.'.planned_start', $item->planned_start) }}"
                       class="block w-full text-sm border-gray-300 rounded-md shadow-sm">
            @endif
        </div>

        {{-- Planned End Date --}}
        <div class="col-span-3">
            @if ($item->children->isEmpty())
                <input type="date" name="items[{{ $item->id }}][planned_end]" 
                       value="{{ old('items.'.$item->id.'.planned_end', $item->planned_end) }}"
                       class="block w-full text-sm border-gray-300 rounded-md shadow-sm">
            @endif
        </div>
    </div>

    {{-- Recurse for children --}}
    @if ($item->children->isNotEmpty())
        <div class="mt-2 space-y-2">
            @foreach ($item->children as $child)
                @include('projects.partials.scheduler-item', ['item' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>