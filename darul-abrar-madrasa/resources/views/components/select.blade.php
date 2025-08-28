@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'required' => false,
    'disabled' => false,
    'help' => null,
    'error' => null
])

@php
    $selectId = $name . '_' . uniqid();
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?? $errors->first($name);
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $selectId }}" class="block text-gray-700 text-sm font-bold mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <select 
        id="{{ $selectId }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge([
            'class' => 'shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline ' . 
                      ($hasError ? 'border-red-500' : '')
        ]) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ old($name, $selected) == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    
    @if($help)
        <p class="text-gray-500 text-xs mt-1">{{ $help }}</p>
    @endif
    
    @if($hasError)
        <p class="text-red-500 text-xs italic mt-1">{{ $errorMessage }}</p>
    @endif
</div>