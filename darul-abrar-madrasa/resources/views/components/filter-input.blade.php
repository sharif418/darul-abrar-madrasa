@props([
    'name',
    'label',
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'options' => [],
    'width' => 'auto'
])

@php
    $widthClasses = [
        'auto' => 'flex-grow md:flex-grow-0',
        'full' => 'w-full',
        'half' => 'w-full md:w-1/2',
        'third' => 'w-full md:w-1/3',
        'quarter' => 'w-full md:w-1/4',
    ][$width] ?? 'flex-grow md:flex-grow-0';
@endphp

<div class="{{ $widthClasses }}">
    @if($label)
        <label for="{{ $name }}" class="block text-gray-700 text-sm font-bold mb-2">{{ $label }}</label>
    @endif
    
    @if($type === 'select')
        <select 
            name="{{ $name }}" 
            id="{{ $name }}" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        >
            <option value="">{{ $placeholder ?? 'Select an option' }}</option>
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" {{ request($name) == $optionValue ? 'selected' : '' }}>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif($type === 'date')
        <input 
            type="date" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            value="{{ request($name) }}"
        >
    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            placeholder="{{ $placeholder }}" 
            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            value="{{ request($name) }}"
        >
    @endif
</div>