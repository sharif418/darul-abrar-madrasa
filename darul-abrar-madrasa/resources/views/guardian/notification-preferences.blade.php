@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notification Preferences</h1>
        <p class="text-sm text-gray-600 mt-1">Manage how you receive notifications about your children</p>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-900">About Notification Preferences</h3>
                <p class="mt-1 text-sm text-blue-700">
                    You can choose to receive notifications via email, SMS, or both for each type of alert. 
                    By default, all notifications are enabled to keep you informed about your children's progress.
                </p>
            </div>
        </div>
    </div>

    <!-- Preferences Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('guardian.notification-preferences.update') }}">
            @csrf

            <div class="space-y-6">
                @foreach($preferences as $type => $data)
                <div class="border border-gray-200 rounded-lg p-5 hover:border-blue-300 transition">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $data['label'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                @if($type === 'low_attendance')
                                    Receive alerts when your child's attendance falls below 75%
                                @elseif($type === 'poor_performance')
                                    Get notified when your child's academic performance needs attention (GPA below 2.5)
                                @elseif($type === 'fee_due')
                                    Reminders for upcoming and overdue fee payments
                                @elseif($type === 'exam_schedule')
                                    Notifications about upcoming exams and schedules
                                @elseif($type === 'result_published')
                                    Alerts when exam results are published
                                @endif
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Email Preference -->
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <input 
                                type="checkbox" 
                                name="preferences[{{ $type }}][email_enabled]" 
                                id="email_{{ $type }}"
                                value="1"
                                {{ $data['preference']->email_enabled ? 'checked' : '' }}
                                class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300"
                            >
                            <label for="email_{{ $type }}" class="ml-3 flex items-center cursor-pointer">
                                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Email Notifications</span>
                            </label>
                        </div>

                        <!-- SMS Preference -->
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <input 
                                type="checkbox" 
                                name="preferences[{{ $type }}][sms_enabled]" 
                                id="sms_{{ $type }}"
                                value="1"
                                {{ $data['preference']->sms_enabled ? 'checked' : '' }}
                                class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300"
                            >
                            <label for="sms_{{ $type }}" class="ml-3 flex items-center cursor-pointer">
                                <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-sm font-medium text-gray-700">SMS Notifications</span>
                            </label>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('guardian.dashboard') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Preferences
                </button>
            </div>
        </form>
    </div>

    <!-- Help Section -->
    <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Need Help?</h3>
        <div class="space-y-2 text-sm text-gray-700">
            <p><strong>Email Notifications:</strong> Detailed messages sent to your registered email address</p>
            <p><strong>SMS Notifications:</strong> Quick alerts sent to your registered phone number</p>
            <p><strong>Note:</strong> You can enable both channels to ensure you never miss important updates</p>
            <p class="mt-4 text-xs text-gray-600">
                For questions or to update your contact information, please contact the administration office.
            </p>
        </div>
    </div>
</div>
@endsection
