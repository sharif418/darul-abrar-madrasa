@extends('layouts.app')

@section('title', 'Notices')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Guardian Dashboard</a>
        <span class="mx-2">/</span>
        <span>Notices</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Notices</h1>
        @isset($guardian)
            <p class="text-gray-600 mt-1">Hello, {{ optional($guardian->user)->name ?? 'Guardian' }}. These notices are for guardians or all users.</p>
        @endisset
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- Notices list --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse(($notices ?? []) as $notice)
            <x-card>
                <div class="flex flex-col h-full">
                    <div class="mb-3">
                        <h2 class="text-lg font-semibold text-gray-800 line-clamp-2">{{ $notice->title ?? 'Untitled Notice' }}</h2>
                        <div class="mt-1 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($notice->publish_date ?? now())->format('Y-m-d') }}
                            @if(!empty($notice->notice_for))
                                <span class="mx-1">&middot;</span>
                                <span class="uppercase tracking-wide text-xs text-gray-500">{{ ucfirst($notice->notice_for) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-gray-700 text-sm flex-1">
                        @php
                            $excerpt = strip_tags($notice->description ?? '');
                            if (mb_strlen($excerpt) > 160) {
                                $excerpt = mb_substr($excerpt, 0, 160) . '…';
                            }
                        @endphp
                        <p>{{ $excerpt ?: 'No description available.' }}</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('notices.public.show', $notice) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            View Notice
                        </a>
                    </div>
                </div>
            </x-card>
        @empty
            <div class="md:col-span-2 lg:col-span-3">
                <x-card>
                    <div class="text-center text-gray-500 py-8">
                        No notices found for guardians at the moment.
                    </div>
                </x-card>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if(isset($notices) && method_exists($notices, 'links'))
        <div class="mt-6">
            {{ $notices->links() }}
        </div>
    @endif

    {{-- Footer links --}}
    <div class="mt-6 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Back to Dashboard</a>
    </div>
</div>
@endsection
