@props([
    'headers' => [],
    'striped' => true,
    'hover' => true,
    'responsive' => true,
    'footer' => null
])

<div {{ $attributes->merge(['class' => $responsive ? 'overflow-x-auto' : '']) }}>
    <table class="min-w-full bg-white">
        <thead>
            <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                @foreach($headers as $header)
                    <th class="py-3 px-6 text-left">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm">
            {{ $slot }}
        </tbody>
        @if($footer)
            <tfoot class="bg-gray-50 text-gray-700 text-sm">
                {{ $footer }}
            </tfoot>
        @endif
    </table>
</div>