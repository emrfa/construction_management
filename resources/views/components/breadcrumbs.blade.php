@props(['items'])

<nav class="flex items-center space-x-2 text-lg mb-4" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
        </svg>
    </a>

    @foreach($items as $index => $item)
        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
        </svg>

        @if($index === count($items) - 1)
            {{-- Last item - not clickable --}}
            <span class="text-gray-800 font-semibold">{{ $item['label'] }}</span>
        @else
            {{-- Clickable items --}}
            <a href="{{ $item['url'] }}" class="text-indigo-600 hover:text-indigo-800 hover:underline transition-colors font-medium">
                {{ $item['label'] }}
            </a>
        @endif
    @endforeach
</nav>
