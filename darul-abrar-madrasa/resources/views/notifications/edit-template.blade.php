@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Template</h1>
            <p class="text-sm text-gray-600 mt-1">{{ ucwords(str_replace('_', ' ', $template->type)) }} - {{ strtoupper($template->channel) }}</p>
        </div>
        <a href="{{ route('notifications.templates') }}" class="btn btn-secondary">
            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('notifications.templates.update', $template) }}">
            @csrf
            @method('PUT')

            <!-- Subject (Email only) -->
            @if($template->channel === 'email')
            <div class="mb-6">
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                    Email Subject <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="subject" 
                    id="subject" 
                    value="{{ old('subject', $template->subject) }}"
                    class="form-input w-full rounded-md border-gray-300 @error('subject') border-red-500 @enderror"
                    required
                >
                @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Use placeholders like {{'{{'}}student_name{{'}}'}}, {{'{{'}}exam_name{{'}}'}}, etc.</p>
            </div>
            @endif

            <!-- Body -->
            <div class="mb-6">
                <label for="body" class="block text-sm font-medium text-gray-700 mb-2">
                    Message Body <span class="text-red-500">*</span>
                </label>
                <textarea 
                    name="body" 
                    id="body" 
                    rows="10"
                    class="form-input w-full rounded-md border-gray-300 font-mono text-sm @error('body') border-red-500 @enderror"
                    required
                >{{ old('body', $template->body) }}</textarea>
                @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1"
                        {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                        class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300"
                    >
                    <span class="ml-2 text-sm font-medium text-gray-700">Template is active</span>
                </label>
                <p class="mt-1 ml-7 text-xs text-gray-500">Inactive templates will not be used for notifications</p>
            </div>

            <!-- Available Variables -->
            @if($template->available_variables && count($template->available_variables) > 0)
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h4 class="text-sm font-semibold text-blue-900 mb-3">Available Placeholders</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    @foreach($template->available_variables as $variable)
                    <div class="flex items-center gap-2">
                        <code class="px-2 py-1 bg-white text-xs text-blue-700 rounded border border-blue-300">{{'{{'}}{{ $variable }}{{'}}'}}</code>
                    </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-blue-700">Copy and paste these placeholders into your template. They will be replaced with actual values when sending.</p>
            </div>
            @endif

            <!-- Help Section -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-900 mb-2">Template Guidelines</h4>
                <ul class="text-xs text-gray-700 space-y-1 list-disc list-inside">
                    <li>Use double curly braces for placeholders: {{'{{'}}variable_name{{'}}'}}</li>
                    <li>Keep messages clear and concise</li>
                    <li>For SMS, keep under 160 characters for single message</li>
                    <li>Test your template after making changes</li>
                    <li>Include relevant contact information if needed</li>
                </ul>
            </div>

            <!-- Preview Section -->
            <div class="mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <h4 class="text-sm font-semibold text-yellow-900 mb-2">Sample Preview</h4>
                <div class="text-sm text-gray-700 whitespace-pre-wrap font-mono bg-white p-3 rounded border border-yellow-300">
                    @if($template->channel === 'email' && $template->subject)
                    <strong>Subject:</strong> {{ str_replace(['{{student_name}}', '{{guardian_name}}'], ['Ahmed Khan', 'Mr. Rahman'], $template->subject) }}
                    <br><br>
                    @endif
                    {{ str_replace(['{{student_name}}', '{{guardian_name}}', '{{attendance_rate}}', '{{gpa}}'], ['Ahmed Khan', 'Mr. Rahman', '68.5', '2.3'], $template->body) }}
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <a href="{{ route('notifications.templates') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Template
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
