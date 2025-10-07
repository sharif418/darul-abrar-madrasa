@props([
    'title' => null,
    'footer' => null,
    'variant' => 'default', // default, bordered, elevated, gradient
    'padding' => 'md', // none, sm, md, lg
    'loading' => false,
    'collapsible' => false,
    'hoverable' => false,
    'icon' => null,
    'accentColor' => null
])

@php
    $paddingClasses = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8'
    ];
    $paddingClass = $paddingClasses[$padding] ?? 'p-6';

    $variantBase = 'rounded-lg overflow-hidden transition-all duration-300';
    $variantClasses = [
        'default' => 'bg-white shadow-md',
        'bordered' => 'bg-white border border-gray-200',
        'elevated' => 'bg-white shadow-lg',
        'gradient' => 'bg-gradient-to-br from-green-500 to-emerald-600 text-white'
    ];
    $containerVariant = $variantClasses[$variant] ?? $variantClasses['default'];

    $hoverClasses = $hoverable ? 'hover:shadow-lg hover:-translate-y-1' : '';

    $accentClass = '';
    if ($accentColor) {
        // Add a colored top border or left border based on need; using top border here
        $accentClass = 'border-t-4 ' . $accentColor;
    }
@endphp

<div x-data="{ expanded: true }"
     {{ $attributes->merge(['class' => "$variantBase $containerVariant $hoverClasses $accentClass"]) }}>
    @if($title)
        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between {{ $variant === 'gradient' ? 'border-white/20' : '' }}">
            <div class="flex items-center">
                @if($icon)
                    <span class="mr-2">{!! $icon !!}</span>
                @endif
                <h3 class="text-lg font-semibold {{ $variant === 'gradient' ? 'text-white' : 'text-gray-800' }}">{{ $title }}</h3>
            </div>
            <div class="flex items-center space-x-2">
                {{ $headerActions ?? '' }}
                @if($collapsible)
                    <button @click="expanded = !expanded" class="{{ $variant === 'gradient' ? 'text-white/80 hover:text-white' : 'text-gray-500 hover:text-gray-700' }}">
                        <svg :class="expanded ? '' : 'rotate-180'" class="w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    @endif

    <div x-show="expanded" x-transition class="{{ $paddingClass }}">
        @if($loading)
            <div class="w-full flex items-center justify-center py-6">
                <svg class="animate-spin -ml-1 mr-3 h-6 w-6 {{ $variant === 'gradient' ? 'text-white' : 'text-green-600' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span class="{{ $variant === 'gradient' ? 'text-white' : 'text-gray-600' }}">Loading...</span>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>

    @if($footer)
        <div class="{{ $variant === 'gradient' ? 'bg-white/10' : 'bg-gray-50' }} px-6 py-3 border-t {{ $variant === 'gradient' ? 'border-white/20' : 'border-gray-200' }}">
            {{ $footer }}
        </div>
    @endif
</div>
