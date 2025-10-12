@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Guardian Dashboard</h1>
                    <p class="text-sm text-gray-600 mt-1">Welcome, {{ optional($guardian->user)->name ?? 'Guardian' }}</p>
                </div>
                <div class="text-right">
                    <a href="{{ route('guardian.children') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        View All Children
                    </a>
                </div>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500">Total Children</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ (int)($totalChildren ?? 0) }}</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500">Total Pending Fees</div>
                <div class="mt-2 text-3xl font-bold text-yellow-600">৳ {{ number_format((float)($totalPendingFees ?? 0), 2) }}</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500">Average Attendance</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">{{ number_format((float)($averageAttendance ?? 0), 2) }}%</div>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500">Upcoming Exams</div>
                <div class="mt-2 text-3xl font-bold text-purple-600">{{ (int) (optional($upcomingExams)->count() ?? 0) }}</div>
            </div>
        </div>

        <!-- Children Overview -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Children Overview</h2>
            @if(isset($students) && $students->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($students as $child)
                        <div class="border rounded-lg p-5">
                            <div class="font-semibold text-gray-900">{{ optional($child->user)->name ?? 'Student #'.$child->id }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                Class: {{ optional($child->class)->name ?? 'N/A' }}
                            </div>
                            <div class="mt-3 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Attendance</span>
                                    <span class="font-semibold">
                                        @php
                                            $rate = method_exists($child, 'getAttendanceRate') ? (float)$child->getAttendanceRate() : 0;
                                        @endphp
                                        {{ number_format($rate, 2) }}%
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-gray-600">Pending Fees</span>
                                    <span class="font-semibold text-yellow-700">
                                        ৳ {{ number_format((float) $child->getPendingFeesAmount(), 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a href="{{ route('guardian.child.profile', $child) }}" class="text-center px-3 py-2 text-sm bg-gray-100 rounded hover:bg-gray-200">Profile</a>
                                <a href="{{ route('guardian.child.attendance', $child) }}" class="text-center px-3 py-2 text-sm bg-gray-100 rounded hover:bg-gray-200">Attendance</a>
                                <a href="{{ route('guardian.child.results', $child) }}" class="text-center px-3 py-2 text-sm bg-gray-100 rounded hover:bg-gray-200">Results</a>
                                <a href="{{ route('guardian.child.fees', $child) }}" class="text-center px-3 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700">Fees</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-500">No children linked to this account yet.</div>
            @endif
        </div>

        <!-- Upcoming Exams -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Exams</h2>
            @if(isset($upcomingExams) && $upcomingExams->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">Exam</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">Class</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">Start Date</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600">End Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($upcomingExams as $exam)
                                <tr>
                                    <td class="px-4 py-2">{{ $exam->name ?? ('Exam #'.$exam->id) }}</td>
                                    <td class="px-4 py-2">{{ optional($exam->class)->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($exam->start_date)->format('d M Y') }}</td>
                                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-sm text-gray-500">No upcoming exams at the moment.</div>
            @endif
        </div>

        <!-- Recent Notices -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Notices</h2>
            @if(isset($recentNotices) && $recentNotices->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($recentNotices as $notice)
                        <div class="border rounded-lg p-4">
                            <div class="font-semibold text-gray-900">{{ $notice->title ?? 'Notice' }}</div>
                            <div class="text-xs text-gray-500 mt-1">Published: {{ \Carbon\Carbon::parse($notice->publish_date)->format('d M Y') }}</div>
                            <div class="text-sm text-gray-700 mt-2 line-clamp-3">{{ $notice->content ?? '' }}</div>
                            <a href="{{ route('notices.public.show', $notice) }}" class="inline-block mt-3 text-green-600 hover:text-green-700 text-sm">View</a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm text-gray-500">No notices available.</div>
            @endif
        </div>
    </div>
</div>
@endsection
