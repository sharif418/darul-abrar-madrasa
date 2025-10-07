@extends('layouts.app')

@section('header', 'Notice Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Notice Details</h1>
        <div class="flex space-x-2">
            <a href="{{ route('notices.edit', $notice->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M7.5 20.5L19 9l-3.5-3.5L4 17v3.5h3.5z"/>
                </svg>
                Edit
            </a>
            <a href="{{ route('notices.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Summary -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white p-4">
                <h2 class="text-xl font-bold">{{ $notice->title }}</h2>
                <p class="text-sm">
                    For:
                    <span class="capitalize">{{ $notice->notice_for }}</span>
                </p>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <div class="text-sm text-gray-500 font-semibold">Published By</div>
                    <div class="text-gray-800">{{ optional($notice->publishedBy)->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-semibold">Publish Date</div>
                    <div class="text-gray-800">{{ \Illuminate\Support\Carbon::parse($notice->publish_date)->format('d M, Y') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-semibold">Expiry Date</div>
                    <div class="text-gray-800">
                        @if($notice->expiry_date)
                            {{ \Illuminate\Support\Carbon::parse($notice->expiry_date)->format('d M, Y') }}
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 font-semibold">Status</div>
                    <div class="mt-1">
                        @if($notice->is_active)
                            <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Active</span>
                        @else
                            <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded-full text-xs">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden lg:col-span-2">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Description</h2>
                <div class="prose max-w-none text-gray-800 whitespace-pre-line">
                    {{ $notice->description }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
