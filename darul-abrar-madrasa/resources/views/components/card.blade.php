@props(['title' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'bg-white shadow-md rounded-lg overflow-hidden']) }}>
    @if($title)
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
        </div>
    @endif
    
    <div class="p-6">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
            {{ $footer }}
        </div>
    @endif
</div>