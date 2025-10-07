@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-800">Teacher Dashboard</h1>
        <a href="{{ route('profile.show') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
            Edit Profile
        </a>
    </div>

    <!-- Profile -->
    <x-card variant="elevated" hoverable="true">
        <x-slot name="title">
            My Profile
        </x-slot>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-center gap-4">
                @if(Auth::user()->avatar)
                    <img class="h-16 w-16 rounded-full ring-2 ring-green-500/40" src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}">
                @else
                    <div class="h-16 w-16 rounded-full bg-green-600 ring-2 ring-green-500/40 flex items-center justify-center text-white text-xl font-bold">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <div class="text-gray-900 font-semibold text-lg">{{ Auth::user()->name }}</div>
                    <div class="text-gray-600 text-sm">{{ Auth::user()->email }}</div>
                    <div class="text-gray-600 text-sm">Designation: <span class="font-medium">{{ $teacher->designation }}</span></div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg border p-4">
                    <div class="text-xs text-gray-500">Department</div>
                    <div class="text-gray-900 font-semibold">{{ optional($teacher->department)->name }}</div>
                </div>
                <div class="rounded-lg border p-4">
                    <div class="text-xs text-gray-500">Phone</div>
                    <div class="text-gray-900 font-semibold">{{ $teacher->phone }}</div>
                </div>
                <div class="rounded-lg border p-4">
                    <div class="text-xs text-gray-500">Qualification</div>
                    <div class="text-gray-900 font-semibold">{{ $teacher->qualification }}</div>
                </div>
                <div class="rounded-lg border p-4">
                    <div class="text-xs text-gray-500">Joining Date</div>
                    <div class="text-gray-900 font-semibold">{{ $teacher->joining_date->format('d M, Y') }}</div>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <a href="{{ route('marks.create') }}" class="rounded-xl p-4 gradient-blue text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11" />
                    </svg>
                </div>
                <div class="font-semibold text-sm text-center">Enter Marks</div>
            </div>
        </a>
        <a href="{{ route('attendances.index') }}" class="rounded-xl p-4 gradient-green text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3M3 11h18" />
                    </svg>
                </div>
                <div class="font-semibold text-sm text-center">Take Attendance</div>
            </div>
        </a>
        <a href="{{ route('results.index') }}" class="rounded-xl p-4 gradient-purple text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12m6-5l2 2 4-4" />
                    </svg>
                </div>
                <div class="font-semibold text-sm text-center">View Results</div>
            </div>
        </a>
        <a href="{{ route('lesson-plans.index') }}" class="rounded-xl p-4 gradient-yellow text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13M7 5h10" />
                    </svg>
                </div>
                <div class="font-semibold text-sm text-center">View My Schedule</div>
            </div>
        </a>
        <a href="{{ route('study-materials.create') }}" class="rounded-xl p-4 gradient-green text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div class="font-semibold text-sm text-center">Upload Material</div>
            </div>
        </a>
        <a href="{{ route('lesson-plans.create') }}" class="rounded-xl p-4 gradient-blue text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6M9 16h6M9 8h6" />
                    </svg>
                </div>
                <div class="font-semibold text-sm text-center">Create Lesson Plan</div>
            </div>
        </a>
    </div>

    <!-- Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card variant="elevated" hoverable="true" title="Attendance Trend (Last 7 Days)" :collapsible="true">
            <div class="chart-container">
                <canvas id="teacherAttendanceChart"></canvas>
            </div>
        </x-card>

        <x-card variant="elevated" hoverable="true" title="Students per Subject" :collapsible="true">
            <div class="chart-container">
                <canvas id="subjectStudentsChart"></canvas>
            </div>
        </x-card>

        <x-card class="lg:col-span-2" variant="elevated" hoverable="true" title="Class Performance (Latest Exam)" :collapsible="true">
            <div class="chart-container" style="height: 360px;">
                <canvas id="classPerformanceChart"></canvas>
            </div>
        </x-card>
    </div>

    <!-- Assigned Subjects -->
    <x-card variant="bordered">
        <x-slot name="title">My Assigned Subjects</x-slot>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Subject</th>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Class</th>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Code</th>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Students</th>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $subject)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-2 px-4 border-b border-gray-200">
                                <div class="inline-flex items-center">
                                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-2"></span>
                                    {{ $subject->name }}
                                </div>
                            </td>
                            <td class="py-2 px-4 border-b border-gray-200">{{ optional($subject->class)->name }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">{{ $subject->code }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-800 text-xs">
                                    {{ $studentCountByClass[$subject->class_id] ?? 0 }}
                                </span>
                            </td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('attendances.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">Take Attendance</a>
                                    <a href="{{ route('marks.create') }}" class="text-green-600 hover:text-green-800 text-sm">Enter Marks</a>
                                    <a href="{{ route('students.index') }}" class="text-gray-700 hover:text-gray-900 text-sm">View Students</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 px-4 text-center text-gray-500">No subjects assigned</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Upcoming Exams -->
    <x-card variant="bordered">
        <x-slot name="title">Upcoming Exams</x-slot>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($upcomingExams as $exam)
                <div class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="font-semibold text-gray-800">{{ $exam->name }}</div>
                    <div class="text-sm text-gray-600">Class: {{ optional($exam->class)->name }}</div>
                    <div class="text-xs text-gray-500">{{ $exam->start_date->format('d M, Y') }} - {{ $exam->end_date->format('d M, Y') }}</div>
                    <div class="mt-2">
                        <a href="{{ route('exams.show', $exam->id) }}" class="inline-block text-green-700 hover:underline text-sm">Enter Results</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4 col-span-full">No upcoming exams</p>
            @endforelse
        </div>
    </x-card>

    <!-- Recent Notices -->
    <x-card variant="bordered">
        <x-slot name="title">Recent Notices</x-slot>
        <div class="space-y-4">
            @forelse($recentNotices as $notice)
                <div class="border rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">{{ $notice->title }}</h3>
                        <span class="text-xs px-2 py-0.5 rounded-full 
                            @if($notice->notice_for == 'all') bg-blue-100 text-blue-800
                            @elseif($notice->notice_for == 'students') bg-green-100 text-green-800
                            @elseif($notice->notice_for == 'teachers') bg-purple-100 text-purple-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($notice->notice_for) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Published on {{ $notice->publish_date->format('d M, Y') }} by {{ optional($notice->publishedBy)->name }}</p>
                    <p class="text-gray-700 mt-2">{{ Str::limit($notice->description, 150) }}</p>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No recent notices</p>
            @endforelse
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Attendance Trend Chart
  const attendanceCtx = document.getElementById('teacherAttendanceChart');
  if (attendanceCtx) {
    new Chart(attendanceCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: @json($recentAttendanceSummary['labels'] ?? []),
        datasets: [{
          label: 'Attendance %',
          data: @json($recentAttendanceSummary['data'] ?? []),
          borderColor: 'rgb(34, 197, 94)',
          backgroundColor: 'rgba(34, 197, 94, 0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, max: 100 } },
        animation: { duration: 1000, easing: 'easeInOutQuart' }
      }
    });
  }

  // Subject-wise Students Chart
  const subjectsCtx = document.getElementById('subjectStudentsChart');
  if (subjectsCtx) {
    new Chart(subjectsCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: @json($subjectWiseStudentCount['labels'] ?? []),
        datasets: [{
          label: 'Number of Students',
          data: @json($subjectWiseStudentCount['data'] ?? []),
          backgroundColor: 'rgba(34, 197, 94, 0.8)',
          borderRadius: 6,
          maxBarThickness: 40
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1000, easing: 'easeInOutQuart' },
        plugins: { legend: { display: false } }
      }
    });
  }

  // Class Performance Radar (optional - empty if not provided)
  const perfCtx = document.getElementById('classPerformanceChart');
  if (perfCtx) {
    new Chart(perfCtx.getContext('2d'), {
      type: 'radar',
      data: {
        labels: @json(($classPerformance['labels'] ?? []) ?? []),
        datasets: [{
          label: 'Average Marks %',
          data: @json(($classPerformance['data'] ?? []) ?? []),
          backgroundColor: 'rgba(34, 197, 94, 0.2)',
          borderColor: 'rgb(34, 197, 94)',
          pointBackgroundColor: 'rgb(34, 197, 94)'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { r: { beginAtZero: true, max: 100 } },
        plugins: { legend: { display: true } }
      }
    });
  }
});
</script>
@endpush
