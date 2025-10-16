@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notification Triggers</h1>
            <p class="text-sm text-gray-600 mt-1">Configure automated notification triggers</p>
        </div>
        <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to History
        </a>
    </div>

    <!-- Triggers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($triggers as $trigger)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r {{ $trigger->is_enabled ? 'from-green-500 to-green-600' : 'from-gray-400 to-gray-500' }}">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white">{{ $trigger->name }}</h3>
                    <form method="POST" action="{{ route('notifications.triggers.update', $trigger) }}" class="inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="is_enabled" value="{{ $trigger->is_enabled ? '0' : '1' }}">
                        <button type="submit" class="px-3 py-1 text-xs font-medium rounded {{ $trigger->is_enabled ? 'bg-white text-green-700 hover:bg-gray-100' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                            {{ $trigger->is_enabled ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-6">
                @if($trigger->description)
                <p class="text-sm text-gray-600 mb-4">{{ $trigger->description }}</p>
                @endif

                <!-- Status -->
                <div class="mb-4">
                    <span class="text-xs font-medium text-gray-500">Status:</span>
                    @if($trigger->is_enabled)
                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Enabled</span>
                    @else
                        <span class="ml-2 px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">Disabled</span>
                    @endif
                </div>

                <!-- Frequency -->
                <div class="mb-4">
                    <span class="text-xs font-medium text-gray-500">Frequency:</span>
                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">{{ ucfirst($trigger->frequency) }}</span>
                </div>

                <!-- Conditions -->
                @if($trigger->conditions && count($trigger->conditions) > 0)
                <div class="mb-4">
                    <h4 class="text-xs font-medium text-gray-500 mb-2">Conditions:</h4>
                    <div class="space-y-2">
                        @foreach($trigger->conditions as $key => $value)
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span class="text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Type Badge -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <span class="px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800">
                        {{ ucwords(str_replace('_', ' ', $trigger->type)) }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($triggers->isEmpty())
    <div class="bg-white rounded-lg shadow-md p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
        </svg>
        <p class="mt-4 text-gray-600">No triggers configured yet</p>
        <p class="mt-2 text-sm text-gray-500">Run the notification seeder to create default triggers</p>
    </div>
    @endif
</div>
@endsection
