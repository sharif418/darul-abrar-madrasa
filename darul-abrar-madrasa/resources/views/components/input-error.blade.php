@props([
    'for' => null,
    'messages' => null,
])

@php
    // If a specific field name is provided via 'for', pull messages from the shared $errors bag.
    $fieldMessages = $for ? $errors->get($for) : null;

    // Prefer explicit 'messages' prop if provided; otherwise use fieldMessages; normalize to array.
    $allMessages = $messages ?? $fieldMessages ?? [];
    if (!is_array($allMessages)) {
        $allMessages = (array) $allMessages;
    }
@endphp

@if (!empty($allMessages))
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach ($allMessages as $message)
            @if($message)
                <li>{{ $message }}</li>
            @endif
        @endforeach
    </ul>
@endif
