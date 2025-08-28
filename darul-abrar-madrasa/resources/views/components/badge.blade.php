@props([
    'color' => 'gray',
    'size' => 'md',
    'rounded' => 'full'
])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'green' => 'bg-green-100 text-green-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'indigo' => 'bg-indigo-100 text-indigo-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'pink' => 'bg-pink-100 text-pink-800',
    ];
    
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
    ];
    
    $roundedOptions = [
        'none' => 'rounded-none',
        'sm' => 'rounded-sm',
        'md' => 'rounded-md',
        'lg' => 'rounded-lg',
        'full' => 'rounded-full',
    ];
    
    $colorClass = $colors[$color] ?? $colors['gray'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $roundedClass = $roundedOptions[$rounded] ?? $roundedOptions['full'];
@endphp

<span {{ $attributes->merge(['class' => "{$colorClass} {$sizeClass} {$roundedClass} inline-flex items-center font-medium"]) }}>
    {{ $slot }}
</span>