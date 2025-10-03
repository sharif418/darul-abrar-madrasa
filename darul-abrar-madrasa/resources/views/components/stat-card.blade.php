@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
    'change' => null,
    'changeType' => 'increase', // 'increase' or 'decrease'
    'changeText' => null,
    'link' => null
])

@php
    $colors = [
        'blue' => [
            'bg' => 'bg-blue-100',
            'text' => 'text-blue-800',
            'icon' => 'text-blue-600',
            'border' => 'border-blue-200',
            'increase' => 'text-green-600',
            'decrease' => 'text-red-600'
        ],
        'green' => [
            'bg' => 'bg-green-100',
            'text' => 'text-green-800',
            'icon' => 'text-green-600',
            'border' => 'border-green-200',
            'increase' => 'text-green-600',
            'decrease' => 'text-red-600'
        ],
        'red' => [
            'bg' => 'bg-red-100',
            'text' => 'text-red-800',
            'icon' => 'text-red-600',
            'border' => 'border-red-200',
            'increase' => 'text-green-600',
            'decrease' => 'text-red-600'
        ],
        'yellow' => [
            'bg' => 'bg-yellow-100',
            'text' => 'text-yellow-800',
            'icon' => 'text-yellow-600',
            'border' => 'border-yellow-200',
            'increase' => 'text-green-600',
            'decrease' => 'text-red-600'
        ],
        'purple' => [
            'bg' => 'bg-purple-100',
            'text' => 'text-purple-800',
            'icon' => 'text-purple-600',
            'border' => 'border-purple-200',
            'increase' => 'text-green-600',
            'decrease' => 'text-red-600'
        ],
        'indigo' => [
            'bg' => 'bg-indigo-100',
            'text' => 'text-indigo-800',
            'icon' => 'text-indigo-600',
            'border' => 'border-indigo-200',
            'increase' => 'text-green-600',
            'decrease' => 'text-red-600'
        ],
        'pink' => [
            'bg' => 'bg-pink-100',
            'text' => 'text-pink-800',
            'icon' => 'text-pink-600',
            'border' => 'border-pink-200',
            'increase' => 'text-green-600',
            'decrease' => 'text-red-600'
        ],
        'gray' => [
            'bg' => 'bg-gray-100',
            'text' => 'text-gray-800',
            'icon' => 'text-gray-600',
            'border' => 'border-gray-200',
            'increase' => 'text-green-600',
            'decrease' => 'text-red-600'
        ],
    ];
    
    $colorClasses = $colors[$color] ?? $colors['blue'];
    $changeClass = $changeType === 'increase' ? $colorClasses['increase'] : $colorClasses['decrease'];
    $changeIcon = $changeType === 'increase' ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6';
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-md overflow-hidden border ' . $colorClasses['border']]) }}>
    <div class="p-5">
        <div class="flex items-center">
            @if($icon)
                <div class="flex-shrink-0 mr-3">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $colorClasses['bg'] }}">
                        <span class="{{ $colorClasses['icon'] }}">{!! $icon !!}</span>
                    </div>
                </div>
            @endif
            <div>
                <p class="text-sm font-medium text-gray-600">{{ $title }}</p>
                <p class="text-2xl font-bold {{ $colorClasses['text'] }}">{{ $value }}</p>
            </div>
        </div>
        
        @if($change !== null || $changeText !== null)
            <div class="mt-4 flex items-center">
                @if($change !== null)
                    <svg class="w-4 h-4 {{ $changeClass }} mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $changeIcon }}"></path>
                    </svg>
                    <span class="text-sm {{ $changeClass }} font-medium">{{ $change }}</span>
                @endif
                
                @if($changeText !== null)
                    <span class="text-sm text-gray-500 ml-1">{{ $changeText }}</span>
                @endif
            </div>
        @endif
        
        @if($link)
            <div class="mt-4">
                <a href="{{ $link }}" class="text-sm font-medium {{ $colorClasses['text'] }} hover:underline">View details →</a>
            </div>
        @endif
    </div>
</div>