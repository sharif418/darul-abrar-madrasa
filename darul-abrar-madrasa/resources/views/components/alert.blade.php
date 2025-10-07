@props([
    'type' => 'info', // success, error, warning, info
    'dismissible' => false,
])

@php
    $colors = [
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    ];
    $iconPaths = [
        'success' => 'M9 12l2 2 4-4M7 20h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z',
        'error' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0ZM12 16.5h.01',
        'warning' => 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z',
        'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-6.219-8.56',
    ];
    $classes = $colors[$type] ?? $colors['info'];
@endphp

<div x-data="{ open: true }" x-show="open" x-transition
     class="border rounded-md p-4 flex items-start gap-3 {{ $classes }}">
    <div class="mt-0.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
             viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="{{ $iconPaths[$type] ?? $iconPaths['info'] }}" />
        </svg>
    </div>
    <div class="flex-1">
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" @click="open = false" class="ml-2 text-current/70 hover:text-current">
            <span class="sr-only">Close</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                 viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.22 5.22a.75.75 0 011.06 0L12 10.94l5.72-5.72a.75.75 0 111.06 1.06L13.06 12l5.72 5.72a.75.75 0 11-1.06 1.06L12 13.06l-5.72 5.72a.75.75 0 11-1.06-1.06L10.94 12 5.22 6.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
            </svg>
        </button>
    @endif
</div>
