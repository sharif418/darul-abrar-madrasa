@props([
    'type' => 'button',
    'color' => 'primary',
    'size' => 'md',
    'href' => null,
    'disabled' => false
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all rounded-md';
    
    $sizeClasses = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
        'xl' => 'px-6 py-3 text-lg',
    ][$size] ?? 'px-4 py-2 text-sm';
    
    $colorClasses = [
        'primary' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
        'secondary' => 'bg-gray-500 hover:bg-gray-600 text-white focus:ring-gray-400',
        'success' => 'bg-green-500 hover:bg-green-600 text-white focus:ring-green-400',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-400',
        'info' => 'bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-400',
        'light' => 'bg-gray-100 hover:bg-gray-200 text-gray-800 focus:ring-gray-300',
        'dark' => 'bg-gray-800 hover:bg-gray-900 text-white focus:ring-gray-700',
        'link' => 'bg-transparent hover:bg-gray-100 text-blue-600 hover:text-blue-700 focus:ring-blue-300',
    ][$color] ?? 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500';
    
    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed' : '';
    
    $classes = "{$baseClasses} {$sizeClasses} {$colorClasses} {$disabledClasses}";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif