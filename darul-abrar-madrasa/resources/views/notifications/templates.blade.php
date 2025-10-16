@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notification Templates</h1>
            <p class="text-sm text-gray-600 mt-1">Customize notification messages for different channels</p>
        </div>
        <a href="{{ route('notifications.index') }}" class="btn btn-secondary">
            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to History
        </a>
    </div>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @php
            $groupedTemplates = $templates->groupBy('type');
            $typeLabels = [
                'low_attendance' => 'Low Attendance Alert',
                'poor_performance' => 'Poor Performance Alert',
                'fee_due' => 'Fee Due Reminder',
                'exam_schedule' => 'Exam Schedule Notification',
                'result_published' => 'Result Publication Alert',
            ];
        @endphp

        @foreach($groupedTemplates as $type => $typeTemplates)
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white">{{ $typeLabels[$type] ?? ucwords(str_replace('_', ' ', $type)) }}</h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($typeTemplates as $template)
                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs font-medium rounded {{ $template->channel === 'email' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                {{ strtoupper($template->channel) }}
                            </span>
                            @if($template->is_active)
                                <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </div>
                        <a href="{{ route('notifications.templates.edit', $template) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Edit
                        </a>
                    </div>

                    @if($template->subject)
                    <div class="mb-2">
                        <p class="text-xs font-medium text-gray-500 mb-1">Subject:</p>
                        <p class="text-sm text-gray-900">{{ Str::limit($template->subject, 60) }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="text-xs font-medium text-gray-500 mb-1">Message Preview:</p>
                        <p class="text-sm text-gray-700">{{ Str::limit($template->body, 100) }}</p>
                    </div>

                    @if($template->available_variables)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs font-medium text-gray-500 mb-1">Available Variables:</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($template->available_variables as $variable)
                            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">{{ '{{' . $variable . '}}' }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="mt-3 text-xs text-gray-500">
                        Last updated: {{ $template->updated_at->format('M d, Y H:i') }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
