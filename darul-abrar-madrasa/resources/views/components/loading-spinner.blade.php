@props([
    'show' => false,
    'size' => 'md',  // sm|md|lg
    'color' => 'blue', // blue|green|red|yellow|gray|indigo|purple
    'text' => 'Loading...',
])

@php
    $sizePx = match($size) {
        'sm' => 24,
        'lg' => 72,
        default => 48,
    };

    $colorClass = match($color) {
        'green' => 'text-green-600',
        'red' => 'text-red-600',
        'yellow' => 'text-yellow-500',
        'gray' => 'text-gray-600',
        'indigo' => 'text-indigo-600',
        'purple' => 'text-purple-600',
        default => 'text-blue-600',
    };
@endphp

<div x-data="{ open: @js($show) }" x-show="open" x-cloak class="fixed inset-0 z-50" role="status" aria-live="polite" aria-label="Loading">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="flex flex-col items-center gap-3 px-6 py-5 bg-white rounded-lg shadow-lg">
            <svg class="animate-spin {{ $colorClass }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="width: {{ $sizePx }}px; height: {{ $sizePx }}px;">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z"></path>
            </svg>
            @if($text)
                <div class="text-sm text-gray-700">{{ $text }}</div>
            @endif
        </div>
    </div>
</div>
