@props([
    'align' => 'right', // left|right
    'width' => 'md', // auto|sm|md|lg
])

@php
    $alignmentClasses = match($align) {
        'left' => 'origin-top-left left-0',
        default => 'origin-top-right right-0',
    };

    $widthClasses = match($width) {
        'sm' => 'w-40',
        'md' => 'w-56',
        'lg' => 'w-72',
        default => 'w-auto',
    };
@endphp

<div x-data="{ open: false, focusIndex: -1 }" class="relative inline-block text-left" @keydown.escape.window="open = false">
    <div @click="open = !open" @keydown.enter.prevent="open = !open" @keydown.space.prevent="open = !open" role="button" aria-haspopup="true" :aria-expanded="open">
        {{ $trigger }}
    </div>

    <div x-cloak x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         @click.away="open = false"
         class="absolute z-30 mt-2 {{ $alignmentClasses }} {{ $widthClasses }} rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
         role="menu" aria-orientation="vertical" tabindex="-1">
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>
