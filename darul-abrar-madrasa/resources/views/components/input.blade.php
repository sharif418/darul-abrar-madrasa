@props([
    'type' => 'text',
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'error' => null
])

@php
    $inputId = $name . '_' . uniqid();
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $inputId }}" class="block text-gray-700 text-sm font-bold mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="{{ $type }}"
        id="{{ $inputId }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $attributes->merge([
            'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ' . 
                      ($hasError ? 'border-red-500' : '')
        ]) }}
    >
    
    @if($help)
        <p class="text-gray-500 text-xs mt-1">{{ $help }}</p>
    @endif
    
    @if($hasError)
        <p class="text-red-500 text-xs italic mt-1">{{ $errorMessage }}</p>
    @endif
</div>