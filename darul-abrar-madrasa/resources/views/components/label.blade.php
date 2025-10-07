@props([
    'for' => null,
    'value' => null,
    'class' => '',
])

<label
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700 '.$class]) }}
>
    {{ $value ?? $slot }}
</label>
