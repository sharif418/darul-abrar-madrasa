@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('guardian.dashboard') }}" class="text-gray-700 hover:text-blue-600">
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('guardian.children') }}" class="ml-1 text-gray-700 hover:text-blue-600">
                        Children
                    </a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('guardian.child.profile', $student) }}" class="ml-1 text-gray-700 hover:text-blue-600">
                        {{ $student->user->name ?? 'Student' }}
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-500">Performance Report</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Performance Report</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $student->user->name ?? 'Student' }} | 
                    {{ $dateRange['start']->format('M d, Y') }} to {{ $dateRange['end']->format('M d, Y') }}
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-3">
                <a href="{{ route('guardian.child.profile', $student) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
                <a href="{{ route('guardian.child.performance-report.download', array_merge(['student' => $student->id], request()->only(['report_type', 'term_start', 'term_end', 'year']))) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF
                </a>
                <button onclick="openEmailModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email Report
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Report Filters</h2>
        <form method="GET" action="{{ route('guardian.child.performance-report', $student) }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                    <select name="report_type" id="report_type" onchange="toggleDateInputs()" 
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="term" {{ $reportType === 'term' ? 'selected' : '' }}>Term Report</option>
                        <option value="annual" {{ $reportType === 'annual' ? 'selected' : '' }}>Annual Report</option>
                    </select>
                </div>
                
                <div id="term_dates" class="{{ $reportType === 'annual' ? 'hidden' : '' }} md:col-span-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                            <input type="date" name="term_start" value="{{ $dateRange['start']->format('Y-m-d') }}"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                            <input type="date" name="term_end" value="{{ $dateRange['end']->format('Y-m-d') }}"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                
                <div id="annual_year" class="{{ $reportType === 'term' ? 'hidden' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                    <select name="year" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" {{ $dateRange['start']->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Generate Report
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Student Info Card -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Student Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600">Student Name</p>
                <p class="text-base font-medium text-gray-900">{{ $student->user->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Roll Number</p>
                <p class="text-base font-medium text-gray-900">{{ $student->roll_number ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Class & Section</p>
                <p class="text-base font-medium text-gray-900">{{ $student->class->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Department</p>
                <p class="text-base font-medium text-gray-900">{{ $student->class->department->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Guardian</p>
                <p class="text-base font-medium text-gray-900">{{ $guardian->user->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Report Generated</p>
                <p class="text-base font-medium text-gray-900">{{ now()->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Academic Performance Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Academic Performance</h2>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Total Exams</p>
                        <p class="text-3xl font-bold text-blue-900">{{ $academicPerformance['totalExams'] }}</p>
                    </div>
                    <svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="bg-{{ $academicPerformance['averageGpa'] >= 3.5 ? 'green' : ($academicPerformance['averageGpa'] >= 2.5 ? 'yellow' : 'red') }}-50 rounded-lg p-4 border border-{{ $academicPerformance['averageGpa'] >= 3.5 ? 'green' : ($academicPerformance['averageGpa'] >= 2.5 ? 'yellow' : 'red') }}-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-{{ $academicPerformance['averageGpa'] >= 3.5 ? 'green' : ($academicPerformance['averageGpa'] >= 2.5 ? 'yellow' : 'red') }}-600 font-medium">Average GPA</p>
                        <p class="text-3xl font-bold text-{{ $academicPerformance['averageGpa'] >= 3.5 ? 'green' : ($academicPerformance['averageGpa'] >= 2.5 ? 'yellow' : 'red') }}-900">{{ number_format($academicPerformance['averageGpa'], 2) }}</p>
                    </div>
                    <svg class="w-12 h-12 text-{{ $academicPerformance['averageGpa'] >= 3.5 ? 'green' : ($academicPerformance['averageGpa'] >= 2.5 ? 'yellow' : 'red') }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-purple-600 font-medium">Pass Rate</p>
                        <p class="text-3xl font-bold text-purple-900">{{ number_format($academicPerformance['passRate'], 1) }}%</p>
                    </div>
                    <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            
            <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-indigo-600 font-medium">Overall Status</p>
                        <p class="text-2xl font-bold text-indigo-900">
                            @if($academicPerformance['averageGpa'] >= 3.5)
                                Excellent
                            @elseif($academicPerformance['averageGpa'] >= 3.0)
                                Very Good
                            @elseif($academicPerformance['averageGpa'] >= 2.5)
                                Good
                            @else
                                Needs Improvement
                            @endif
                        </p>
                    </div>
                    <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Exam Results Table -->
        @if($academicPerformance['examResults']->count() > 0)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Exam-wise Results</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Marks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg GPA</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($academicPerformance['examResults'] as $examData)
                        <tr class="{{ $examData['summary']['status'] === 'Passed' ? 'bg-green-50' : 'bg-red-50' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $examData['exam']->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($examData['exam']->end_date ?? $examData['exam']->start_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($examData['summary']['totalMarks'], 2) }} / {{ number_format($examData['summary']['totalFullMarks'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($examData['summary']['percentage'], 2) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($examData['summary']['averageGpa'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $examData['summary']['status'] === 'Passed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $examData['summary']['status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Subject Performance Chart -->
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Subject Performance</h3>
            <canvas id="subjectPerformanceChart" height="80"></canvas>
        </div>
        @else
        <p class="text-gray-500 text-center py-8">No exam results available for the selected period.</p>
        @endif
    </div>

    <!-- Attendance Summary Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Attendance Summary</h2>
        
        <!-- Attendance Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p class="text-sm text-gray-600 font-medium">Total Days</p>
                <p class="text-3xl font-bold text-gray-900">{{ $attendanceSummary['term']['totalDays'] }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <p class="text-sm text-green-600 font-medium">Present Days</p>
                <p class="text-3xl font-bold text-green-900">{{ $attendanceSummary['term']['presentDays'] }}</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <p class="text-sm text-red-600 font-medium">Absent Days</p>
                <p class="text-3xl font-bold text-red-900">{{ $attendanceSummary['term']['absentDays'] }}</p>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <p class="text-sm text-blue-600 font-medium">Attendance Rate</p>
                <p class="text-3xl font-bold text-blue-900">{{ number_format($attendanceSummary['term']['attendanceRate'], 1) }}%</p>
            </div>
        </div>

        <!-- Attendance Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Attendance Breakdown</h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Present</span>
                            <span class="font-medium">{{ $attendanceSummary['term']['presentDays'] }} days ({{ $attendanceSummary['term']['totalDays'] > 0 ? number_format(($attendanceSummary['term']['presentDays'] / $attendanceSummary['term']['totalDays']) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $attendanceSummary['term']['totalDays'] > 0 ? ($attendanceSummary['term']['presentDays'] / $attendanceSummary['term']['totalDays']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Absent</span>
                            <span class="font-medium">{{ $attendanceSummary['term']['absentDays'] }} days ({{ $attendanceSummary['term']['totalDays'] > 0 ? number_format(($attendanceSummary['term']['absentDays'] / $attendanceSummary['term']['totalDays']) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-red-600 h-2 rounded-full" style="width: {{ $attendanceSummary['term']['totalDays'] > 0 ? ($attendanceSummary['term']['absentDays'] / $attendanceSummary['term']['totalDays']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Late</span>
                            <span class="font-medium">{{ $attendanceSummary['term']['lateDays'] }} days ({{ $attendanceSummary['term']['totalDays'] > 0 ? number_format(($attendanceSummary['term']['lateDays'] / $attendanceSummary['term']['totalDays']) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ $attendanceSummary['term']['totalDays'] > 0 ? ($attendanceSummary['term']['lateDays'] / $attendanceSummary['term']['totalDays']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Leave</span>
                            <span class="font-medium">{{ $attendanceSummary['term']['leaveDays'] }} days ({{ $attendanceSummary['term']['totalDays'] > 0 ? number_format(($attendanceSummary['term']['leaveDays'] / $attendanceSummary['term']['totalDays']) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $attendanceSummary['term']['totalDays'] > 0 ? ($attendanceSummary['term']['leaveDays'] / $attendanceSummary['term']['totalDays']) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Attendance Rate Indicator</h3>
                <div class="flex items-center justify-center h-48">
                    <div class="relative">
                        <svg class="transform -rotate-90 w-48 h-48">
                            <circle cx="96" cy="96" r="80" stroke="#e5e7eb" stroke-width="12" fill="none" />
                            <circle cx="96" cy="96" r="80" 
                                    stroke="{{ $attendanceSummary['term']['attendanceRate'] >= 90 ? '#10b981' : ($attendanceSummary['term']['attendanceRate'] >= 75 ? '#f59e0b' : '#ef4444') }}" 
                                    stroke-width="12" 
                                    fill="none"
                                    stroke-dasharray="{{ 2 * 3.14159 * 80 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 80 * (1 - $attendanceSummary['term']['attendanceRate'] / 100) }}"
                                    stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-4xl font-bold text-gray-900">{{ number_format($attendanceSummary['term']['attendanceRate'], 1) }}%</div>
                                <div class="text-sm text-gray-600">Attendance</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Status Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Fee Status</h2>
        
        <!-- Fee Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p class="text-sm text-gray-600 font-medium">Total Fees</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($feeStatus['totalFees'], 2) }} BDT</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <p class="text-sm text-green-600 font-medium">Paid Amount</p>
                <p class="text-2xl font-bold text-green-900">{{ number_format($feeStatus['paidAmount'], 2) }} BDT</p>
            </div>
            <div class="bg-{{ $feeStatus['pendingAmount'] > 0 ? 'red' : 'green' }}-50 rounded-lg p-4 border border-{{ $feeStatus['pendingAmount'] > 0 ? 'red' : 'green' }}-200">
                <p class="text-sm text-{{ $feeStatus['pendingAmount'] > 0 ? 'red' : 'green' }}-600 font-medium">Pending Amount</p>
                <p class="text-2xl font-bold text-{{ $feeStatus['pendingAmount'] > 0 ? 'red' : 'green' }}-900">{{ number_format($feeStatus['pendingAmount'], 2) }} BDT</p>
            </div>
        </div>

        <!-- Fee Details Table -->
        @if($feeStatus['fees']->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($feeStatus['fees'] as $fee)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $fee->fee_type ?? 'General Fee' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($fee->net_amount ?? $fee->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($fee->paid_amount ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format(max(0, ($fee->net_amount ?? $fee->amount) - ($fee->paid_amount ?? 0)), 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($fee->due_date)->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $fee->status === 'paid' ? 'bg-green-100 text-green-800' : ($fee->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($fee->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($feeStatus['pendingAmount'] > 0)
        <div class="mt-4">
            <a href="{{ route('guardian.child.fees', $student) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Pay Pending Fees
            </a>
        </div>
        @endif
        @else
        <p class="text-gray-500 text-center py-8">No fee records available for the selected period.</p>
        @endif
    </div>

    <!-- Teacher Remarks Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Teacher Remarks</h2>
        
        @if($teacherRemarks->count() > 0)
        <div class="space-y-4">
            @foreach($teacherRemarks as $remark)
            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900">{{ $remark['subject'] }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $remark['exam'] }}</p>
                        <p class="text-sm text-gray-800 mt-2">{{ $remark['remarks'] }}</p>
                    </div>
                    <div class="ml-4 text-right">
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($remark['date'])->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-500 text-center py-8">No teacher remarks available for this period.</p>
        @endif
    </div>

    <!-- Recommendations Section -->
    @if(count($recommendations) > 0)
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Recommendations</h2>
        
        <div class="space-y-4">
            @foreach($recommendations as $recommendation)
            <div class="border-l-4 border-{{ $recommendation['priority'] === 'High' ? 'red' : ($recommendation['priority'] === 'Medium' ? 'yellow' : 'green') }}-500 bg-{{ $recommendation['priority'] === 'High' ? 'red' : ($recommendation['priority'] === 'Medium' ? 'yellow' : 'green') }}-50 p-4 rounded-r-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        @if($recommendation['type'] === 'attendance' || $recommendation['type'] === 'academic' || $recommendation['type'] === 'financial')
                        <svg class="h-6 w-6 text-{{ $recommendation['priority'] === 'High' ? 'red' : ($recommendation['priority'] === 'Medium' ? 'yellow' : 'green') }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        @else
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @endif
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900">{{ $recommendation['title'] }}</h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $recommendation['priority'] === 'High' ? 'bg-red-100 text-red-800' : ($recommendation['priority'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ $recommendation['priority'] }} Priority
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-700">{{ $recommendation['description'] }}</p>
                        <p class="mt-2 text-sm text-gray-600 italic">Action: {{ $recommendation['action'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Email Modal -->
<div id="emailModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEmailModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form method="POST" action="{{ route('guardian.child.performance-report.email', $student) }}">
                @csrf
                <input type="hidden" name="report_type" value="{{ $reportType }}">
                <input type="hidden" name="term_start" value="{{ $dateRange['start']->format('Y-m-d') }}">
                <input type="hidden" name="term_end" value="{{ $dateRange['end']->format('Y-m-d') }}">
                @if($reportType === 'annual')
                <input type="hidden" name="year" value="{{ $dateRange['start']->year }}">
                @endif
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Email Performance Report
                            </h3>
                            <div class="mt-4">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address
                                </label>
                                <input type="email" name="email" id="email" required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Enter email address">
                                <p class="mt-2 text-sm text-gray-500">
                                    The performance report will be sent as a PDF attachment to this email address.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Send Email
                    </button>
                    <button type="button" onclick="closeEmailModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Toggle date inputs based on report type
    function toggleDateInputs() {
        const reportType = document.getElementById('report_type').value;
        const termDates = document.getElementById('term_dates');
        const annualYear = document.getElementById('annual_year');
        
        if (reportType === 'annual') {
            termDates.classList.add('hidden');
            annualYear.classList.remove('hidden');
        } else {
            termDates.classList.remove('hidden');
            annualYear.classList.add('hidden');
        }
    }

    // Email modal functions
    function openEmailModal() {
        document.getElementById('emailModal').classList.remove('hidden');
    }

    function closeEmailModal() {
        document.getElementById('emailModal').classList.add('hidden');
    }

    // Subject Performance Chart
    @if($academicPerformance['subjectPerformance']->count() > 0)
    const subjectLabels = @json($academicPerformance['subjectPerformance']->pluck('subject.name')->values());
    const subjectMarks = @json($academicPerformance['subjectPerformance']->pluck('average_marks')->values());
    const subjectGpas = @json($academicPerformance['subjectPerformance']->pluck('average_gpa')->values());

    const ctx = document.getElementById('subjectPerformanceChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: subjectLabels,
                datasets: [{
                    label: 'Average Marks',
                    data: subjectMarks,
                    backgroundColor: subjectGpas.map(gpa => {
                        if (gpa >= 3.5) return 'rgba(34, 197, 94, 0.7)';
                        if (gpa >= 2.5) return 'rgba(251, 191, 36, 0.7)';
                        return 'rgba(239, 68, 68, 0.7)';
                    }),
                    borderColor: subjectGpas.map(gpa => {
                        if (gpa >= 3.5) return 'rgb(34, 197, 94)';
                        if (gpa >= 2.5) return 'rgb(251, 191, 36)';
                        return 'rgb(239, 68, 68)';
                    }),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Average Performance by Subject'
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const gpa = subjectGpas[context.dataIndex];
                                return 'GPA: ' + gpa.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Average Marks'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Subjects'
                        }
                    }
                }
            }
        });
    }
    @endif
</script>
@endpush

@endsection
