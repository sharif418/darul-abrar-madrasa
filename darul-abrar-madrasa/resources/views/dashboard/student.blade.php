@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-800">Student Dashboard</h1>
        <a href="{{ route('profile.show') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
            Edit Profile
        </a>
    </div>

    <!-- Profile + Quick Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-card variant="elevated" hoverable="true" class="lg:col-span-2">
            <x-slot name="title">My Profile</x-slot>
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
                        <div class="text-gray-600 text-sm">Class: <span class="font-medium">{{ optional($student->class)->name }}</span></div>
                        <div class="text-gray-600 text-sm">Roll: <span class="font-medium">{{ $student->roll_number }}</span></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-lg border p-4">
                        <div class="text-xs text-gray-500">Current GPA</div>
                        <div class="text-gray-900 font-semibold">
                            {{ number_format(($gpaTrend['data'][count($gpaTrend['data'])-1] ?? ($student->getCurrentGpa() ?? 0)), 2) }}
                        </div>
                        @php
                            $gNow = $gpaTrend['data'][count($gpaTrend['data'])-1] ?? null;
                            $gPrev = $gpaTrend['data'][count($gpaTrend['data'])-2] ?? null;
                            $delta = ($gNow !== null && $gPrev !== null) ? round($gNow - $gPrev, 2) : null;
                        @endphp
                        @if(!is_null($delta))
                            <div class="text-xs {{ $delta >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $delta >= 0 ? '+' : '' }}{{ $delta }} from last exam
                            </div>
                        @endif
                    </div>
                    <div class="rounded-lg border p-4">
                        <div class="text-xs text-gray-500">Attendance (This Month)</div>
                        <div class="text-gray-900 font-semibold">{{ $attendancePercentage }}%</div>
                        <div class="text-xs text-gray-600">Present {{ $presentCount }} of {{ $attendanceCount }}</div>
                    </div>
                    <div class="rounded-lg border p-4">
                        <div class="text-xs text-gray-500">Best Subject</div>
                        <div class="text-gray-900 font-semibold">{{ $bestSubject ?? 'N/A' }}</div>
                    </div>
                    <div class="rounded-lg border p-4">
                        <div class="text-xs text-gray-500">Needs Improvement</div>
                        <div class="text-gray-900 font-semibold">{{ $worstSubject ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </x-card>

        <x-card variant="elevated" hoverable="true">
            <x-slot name="title">Quick Stats</x-slot>
            <div class="grid grid-cols-2 gap-4">
                <x-stat-card title="Upcoming Exams" :value="$upcomingExams->count()" color="yellow" clickable="true" link="{{ route('results.index') }}">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3M3 11h18" />
                        </svg>
                    </x-slot>
                </x-stat-card>
                <x-stat-card title="Pending Fees" :value="$pendingFees->count()" color="red" clickable="true" link="{{ route('my.fees') }}">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2" />
                        </svg>
                    </x-slot>
                </x-stat-card>
                <x-stat-card title="Results Entries" :value="$recentResults->count()" color="blue" clickable="true" link="{{ route('my.results') }}">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4" />
                        </svg>
                    </x-slot>
                </x-stat-card>
                <x-stat-card title="Materials" value="View" color="green" clickable="true" link="{{ route('my.materials') }}">
                    <x-slot name="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13" />
                        </svg>
                    </x-slot>
                </x-stat-card>
            </div>
        </x-card>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <a href="{{ route('my.results') }}" class="rounded-xl p-4 gradient-blue text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M9 12l2 2 4-4"/></svg>
                </div>
                <div class="font-semibold text-sm text-center">My Results</div>
            </div>
        </a>
        <a href="{{ route('my.materials') }}" class="rounded-xl p-4 gradient-green text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M12 6.253v13"/></svg>
                </div>
                <div class="font-semibold text-sm text-center">Study Materials</div>
            </div>
        </a>
        <a href="{{ route('my.attendance') }}" class="rounded-xl p-4 gradient-purple text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M8 7V3m8 4V3M3 11h18"/></svg>
                </div>
                <div class="font-semibold text-sm text-center">My Attendance</div>
            </div>
        </a>
        <a href="{{ route('my.fees') }}" class="rounded-xl p-4 gradient-yellow text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M12 8c-1.657 0-3 .895-3 2"/></svg>
                </div>
                <div class="font-semibold text-sm text-center">My Fees</div>
            </div>
        </a>
        <a href="#" class="rounded-xl p-4 gradient-green text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M12 4v16m8-8H4"/></svg>
                </div>
                <div class="font-semibold text-sm text-center">Download ID Card</div>
            </div>
        </a>
        <a href="{{ route('lesson_plans.index') }}" class="rounded-xl p-4 gradient-blue text-white hover-lift">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                         d="M9 12h6M9 16h6M9 8h6"/></svg>
                </div>
                <div class="font-semibold text-sm text-center">View Timetable</div>
            </div>
        </a>
    </div>

    <!-- Attendance Trend -->
    <x-card variant="elevated" hoverable="true" title="Attendance Trend (Last 30 Days)" :collapsible="true">
        <div class="chart-container">
            <canvas id="studentAttendanceChart"></canvas>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mt-4">
            @if(!empty($monthlyAttendancePercent['labels'] ?? []))
                @foreach(($monthlyAttendancePercent['labels'] ?? []) as $i => $label)
                    <div class="rounded-lg border p-3 text-center">
                        <div class="text-xs text-gray-500">{{ $label }}</div>
                        <div class="text-gray-900 font-semibold">
                            {{ $monthlyAttendancePercent['data'][$i] ?? 0 }}%
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </x-card>

    <!-- Academic Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card variant="elevated" hoverable="true" title="Subject-wise Performance (Latest Exam)" :collapsible="true">
            <div class="chart-container" style="height: 360px;">
                <canvas id="subjectPerformanceChart"></canvas>
            </div>
        </x-card>
        <x-card variant="elevated" hoverable="true" title="GPA Trend (Last Exams)" :collapsible="true">
            <div class="chart-container" style="height: 360px;">
                <canvas id="gpaTrendChart"></canvas>
            </div>
        </x-card>
    </div>

    <!-- Upcoming Exams and Recent Results -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card variant="bordered">
            <x-slot name="title">Upcoming Exams</x-slot>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($upcomingExams as $exam)
                    <div x-data="{
                            start: new Date('{{ $exam->start_date->format('Y-m-d') }}T00:00:00').getTime(),
                            now: Date.now(),
                            get remaining() {
                                const d = Math.max(0, this.start - this.now);
                                const days = Math.floor(d / (1000*60*60*24));
                                const hrs = Math.floor((d % (1000*60*60*24))/(1000*60*60));
                                return `${days}d ${hrs}h`;
                            },
                            tick() { this.now = Date.now(); }
                        }"
                        x-init="setInterval(() => tick(), 60000)"
                        class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="font-semibold text-gray-800">{{ $exam->name }}</div>
                        <div class="text-sm text-gray-600">Class: {{ optional($exam->class)->name }}</div>
                        <div class="text-xs text-gray-500">{{ $exam->start_date->format('d M, Y') }} - {{ $exam->end_date->format('d M, Y') }}</div>
                        <div class="mt-2 text-sm"
                             :class="{
                                'text-green-600': (new Date('{{ $exam->start_date->format('Y-m-d') }}') - new Date())/1000/60/60/24 > 14,
                                'text-yellow-600': (new Date('{{ $exam->start_date->format('Y-m-d') }}') - new Date())/1000/60/60/24 <= 14 && (new Date('{{ $exam->start_date->format('Y-m-d') }}') - new Date())/1000/60/60/24 >= 7,
                                'text-red-600': (new Date('{{ $exam->start_date->format('Y-m-d') }}') - new Date())/1000/60/60/24 < 7
                             }" x-text="remaining"></div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4 col-span-full">No upcoming exams</p>
                @endforelse
            </div>
        </x-card>

        <x-card variant="bordered">
            <x-slot name="title">Recent Results</x-slot>
            <div class="space-y-3">
                @forelse($recentResults as $result)
                    <div class="p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $result->subject->name }}</div>
                                <div class="text-xs text-gray-500">{{ $result->exam->name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm">Marks:
                                    <span class="font-semibold">{{ $result->marks_obtained }}/{{ $result->subject->full_mark }}</span>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    @class([
                                        'bg-green-100 text-green-800' => in_array($result->grade, ['A+', 'A', 'A-']),
                                        'bg-yellow-100 text-yellow-800' => in_array($result->grade, ['B', 'B-']),
                                        'bg-red-100 text-red-800' => in_array($result->grade, ['C', 'D', 'F']),
                                        'bg-gray-100 text-gray-800' => !in_array($result->grade, ['A+','A','A-','B','B-','C','D','F'])
                                    ])">
                                    {{ $result->grade }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No recent results</p>
                @endforelse
                <div class="text-center">
                    <a href="{{ route('my.results') }}" class="text-green-700 hover:underline text-sm">View All Results</a>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Pending Fees with Timeline -->
    <x-card variant="elevated" hoverable="true" title="Fees Timeline & Pending Dues" :collapsible="true">
        <div class="chart-container" style="height: 320px;">
            <canvas id="feeTimelineChart"></canvas>
        </div>
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Fee Type</th>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Due Date</th>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingFees as $fee)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-2 px-4 border-b border-gray-200">{{ ucfirst($fee->fee_type) }}</td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                <div>{{ number_format($fee->amount, 2) }}</div>
                                @if($fee->status == 'partial')
                                    <div class="text-xs text-gray-500">Paid: {{ number_format($fee->paid_amount, 2) }}</div>
                                    <div class="text-xs text-red-500">Due: {{ number_format($fee->amount - $fee->paid_amount, 2) }}</div>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                {{ optional($fee->due_date)->format('d M, Y') }}
                                @if(method_exists($fee, 'getIsOverdueAttribute') ? $fee->isOverdue : false)
                                    <span class="text-xs text-red-500 font-medium block">OVERDUE</span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                @if($fee->status == 'paid')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                @elseif($fee->status == 'partial')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Unpaid</span>
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b border-gray-200">
                                @php
                                    $pct = method_exists($fee, 'getPaymentProgressPercentage') ? $fee->getPaymentProgressPercentage() : 0;
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ $pct }}%</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 px-4 text-center text-gray-500">No pending fees</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Recent Notices -->
    <x-card variant="bordered">
        <x-slot name="title">Recent Notices</x-slot>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($recentNotices as $notice)
                <div class="bg-white border rounded-lg p-4">
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
                    <p class="text-sm text-gray-600 mt-1">Published on {{ $notice->publish_date->format('d M, Y') }}</p>
                    <p class="text-gray-700 mt-2">{{ Str::limit($notice->description, 100) }}</p>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4 col-span-full">No recent notices</p>
            @endforelse
        </div>
        <div class="mt-4 text-center">
            <a href="{{ route('notices.public') }}" class="inline-block text-green-700 hover:underline text-sm">View All Notices</a>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Attendance Trend Chart
  const attendanceCtx = document.getElementById('studentAttendanceChart');
  if (attendanceCtx) {
    new Chart(attendanceCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: @json($attendanceTrend['labels'] ?? []),
        datasets: [{
          label: 'Attendance',
          data: @json($attendanceTrend['data'] ?? []),
          borderColor: 'rgb(34, 197, 94)',
          backgroundColor: 'rgba(34, 197, 94, 0.1)',
          fill: true,
          tension: 0.4
        }, {
          label: 'Target (75%)',
          data: Array(@json(count($attendanceTrend['labels'] ?? []))).fill(75),
          borderColor: 'rgb(239, 68, 68)',
          borderDash: [5, 5],
          fill: false
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

  // Subject Performance Radar Chart
  const performanceCtx = document.getElementById('subjectPerformanceChart');
  if (performanceCtx) {
    new Chart(performanceCtx.getContext('2d'), {
      type: 'radar',
      data: {
        labels: @json($subjectWisePerformance['labels'] ?? []),
        datasets: [{
          label: 'My Performance',
          data: @json($subjectWisePerformance['data'] ?? []),
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

  // GPA Trend Chart
  const gpaCtx = document.getElementById('gpaTrendChart');
  if (gpaCtx) {
    new Chart(gpaCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: @json($gpaTrend['labels'] ?? []),
        datasets: [{
          label: 'GPA',
          data: @json($gpaTrend['data'] ?? []),
          borderColor: 'rgb(59, 130, 246)',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, max: 5 } },
        animation: { duration: 1000, easing: 'easeInOutQuart' }
      }
    });
  }

  // Fee Payment Timeline (Due vs Paid)
  const feeCtx = document.getElementById('feeTimelineChart');
  if (feeCtx) {
    new Chart(feeCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: @json($feePaymentTimeline['labels'] ?? []),
        datasets: [
          {
            label: 'Due Amount',
            data: @json($feePaymentTimeline['due'] ?? []),
            backgroundColor: 'rgba(239, 68, 68, 0.8)',
            borderRadius: 6,
            maxBarThickness: 28
          },
          {
            label: 'Paid Amount',
            data: @json($feePaymentTimeline['data'] ?? []),
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderRadius: 6,
            maxBarThickness: 28
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 1000, easing: 'easeInOutQuart' },
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
      }
    });
  }
});
</script>
@endpush
