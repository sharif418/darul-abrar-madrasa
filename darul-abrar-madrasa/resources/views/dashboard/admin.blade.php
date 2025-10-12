@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 space-y-8">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" x-data>
        <x-stat-card class="stat-card-hover"
            title="Total Students"
            :value="$totalStudents"
            color="green"
            :clickable="true"
            link="{{ route('students.index') }}"
            tooltip="Total number of enrolled students">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card class="stat-card-hover"
            title="Total Teachers"
            :value="$totalTeachers"
            color="blue"
            :clickable="true"
            link="{{ route('teachers.index') }}"
            tooltip="Total number of active teachers">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card class="stat-card-hover"
            title="Total Classes"
            :value="$totalClasses"
            color="yellow"
            :clickable="true"
            link="{{ route('classes.index') }}"
            tooltip="Total classes running">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card class="stat-card-hover"
            title="Fees Collected (৳)"
            :value="number_format($totalFeesCollected, 2)"
            color="purple"
            :clickable="true"
            link="{{ route('fees.index') }}"
            tooltip="Total amount collected to date">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card class="stat-card-hover"
            title="Pending Fees (৳)"
            :value="number_format($pendingFees, 2)"
            color="red"
            :clickable="true"
            link="{{ route('fees.index') }}"
            tooltip="Unpaid + Partial due amount">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card class="stat-card-hover"
            title="Total Subjects"
            :value="$totalSubjects"
            color="indigo"
            :clickable="true"
            link="{{ route('subjects.index') }}"
            tooltip="Total number of subjects">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card class="stat-card-hover"
            title="Departments"
            :value="$totalDepartments"
            color="gray"
            :clickable="true"
            link="{{ route('departments.index') }}"
            tooltip="Total departments">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7h18M3 12h18M3 17h18" />
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card class="stat-card-hover"
            title="Users"
            :value="$totalUsers"
            color="blue"
            :clickable="true"
            link="{{ route('users.index') }}"
            tooltip="All user accounts">
            <x-slot name="icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 10a4 4 0 110-8" />
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card variant="elevated" hoverable="true" title="Fee Collection Trend (Last 6 Months)" :collapsible="true">
            <div class="chart-container">
                <canvas id="feeCollectionChart"></canvas>
            </div>
        </x-card>

        <x-card variant="elevated" hoverable="true" title="Student Distribution by Department" :collapsible="true">
            <div class="chart-container">
                <canvas id="studentDistributionChart"></canvas>
            </div>
        </x-card>

        <x-card class="lg:col-span-2" variant="elevated" hoverable="true" title="Attendance Overview (This Month)" :collapsible="true">
            <div class="chart-container" style="height: 360px;">
                <canvas id="attendanceChart"></canvas>
            </div>
        </x-card>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="{{ route('students.create') }}" class="rounded-xl p-6 gradient-green text-white hover-lift">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-lg">Add New Student</div>
                    <div class="text-white/80 text-sm">Enroll a new student</div>
                </div>
            </div>
        </a>

        <a href="{{ route('notices.create') }}" class="rounded-xl p-6 gradient-blue text-white hover-lift">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h8m-8 4h6" />
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-lg">Create Notice</div>
                    <div class="text-white/80 text-sm">Publish a new notice</div>
                </div>
            </div>
        </a>

        <a href="{{ route('fees.index') }}" class="rounded-xl p-6 gradient-purple text-white hover-lift">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-2.28 0-4 .895-4 2s1.72 2 4 2 4 .895 4 2" />
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-lg">Record Fee Payment</div>
                    <div class="text-white/80 text-sm">Add or update payments</div>
                </div>
            </div>
        </a>

        <a href="{{ route('exams.create') }}" class="rounded-xl p-6 gradient-yellow text-white hover-lift">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12h6M9 16h6M9 8h6M5 7h14" />
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-lg">Add Exam</div>
                    <div class="text-white/80 text-sm">Schedule a new exam</div>
                </div>
            </div>
        </a>

        <a href="{{ route('users.index') }}" class="rounded-xl p-6 gradient-blue text-white hover-lift">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857" />
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-lg">Manage Users</div>
                    <div class="text-white/80 text-sm">Users and permissions</div>
                </div>
            </div>
        </a>

        <a href="{{ route('fees.reports') }}" class="rounded-xl p-6 gradient-green text-white hover-lift">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3v18m-8-8h18" />
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-lg">View Reports</div>
                    <div class="text-white/80 text-sm">Financial reports</div>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.system-health') }}" class="rounded-xl p-6 gradient-indigo text-white hover-lift">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-lg">System Health</div>
                    <div class="text-white/80 text-sm">Monitor system integrity</div>
                </div>
            </div>
        </a>
    </div>

    <!-- Tables: Recent Fees and Upcoming Exams -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card variant="bordered">
            <x-slot name="title">Recent Fee Collections</x-slot>
            <div x-data="{
                    search: '',
                    sortKey: 'date',
                    sortAsc: false,
                    get rows() {
                        const data = [...this._rows];
                        const filtered = data.filter(r =>
                            r.student.toLowerCase().includes(this.search.toLowerCase())
                        );
                        const key = this.sortKey;
                        filtered.sort((a, b) => {
                            if (a[key] < b[key]) return this.sortAsc ? -1 : 1;
                            if (a[key] > b[key]) return this.sortAsc ? 1 : -1;
                            return 0;
                        });
                        return filtered;
                    },
                    _rows: [
                        @foreach($recentFees as $fee)
                        {
                            student: '{{ $fee->student->user->name }}',
                            amount: {{ (float) $fee->amount }},
                            date: '{{ ($fee->payment_date ?? $fee->due_date) ? ($fee->payment_date ?? $fee->due_date) : '' }}',
                            status: '{{ $fee->status }}',
                            paid: {{ (float) $fee->paid_amount }},
                        },
                        @endforeach
                    ]
                }" class="space-y-4">
                <div class="flex items-center justify-between">
                    <input x-model="search" type="text" placeholder="Search by student..."
                        class="w-56 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:border-green-500" />
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-600">Sort by:</span>
                        <button @click="sortKey = 'student'; sortAsc = !sortAsc"
                            class="px-2 py-1 rounded hover:bg-gray-100">Student</button>
                        <button @click="sortKey = 'amount'; sortAsc = !sortAsc"
                            class="px-2 py-1 rounded hover:bg-gray-100">Amount</button>
                        <button @click="sortKey = 'date'; sortAsc = !sortAsc"
                            class="px-2 py-1 rounded hover:bg-gray-100">Date</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Student</th>
                                <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                                <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="py-2 px-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(r, idx) in rows" :key="idx">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-2 px-4 border-b border-gray-200" x-text="r.student"></td>
                                    <td class="py-2 px-4 border-b border-gray-200" x-text="Number(r.amount).toFixed(2)"></td>
                                    <td class="py-2 px-4 border-b border-gray-200" x-text="r.date"></td>
                                    <td class="py-2 px-4 border-b border-gray-200">
                                        <span x-show="r.status === 'paid'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                        <span x-show="r.status === 'partial'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                                        <span x-show="r.status === 'unpaid'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Unpaid</span>
                                    </td>
                                </tr>
                            </template>
                            @if($recentFees->isEmpty())
                                <tr>
                                    <td colspan="4" class="py-4 px-4 border-b border-gray-200 text-center text-gray-500">No recent fee collections</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>

        <x-card variant="bordered">
            <x-slot name="title">Upcoming Exams</x-slot>
            <div class="space-y-4">
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
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $exam->name }}</div>
                                <div class="text-sm text-gray-600">Class: {{ optional($exam->class)->name }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $exam->start_date->format('d M, Y') }} - {{ $exam->end_date->format('d M, Y') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium"
                                     :class="{
                                        'text-green-600': (new Date('{{ $exam->start_date->format('Y-m-d') }}') - new Date())/1000/60/60/24 > 7,
                                        'text-yellow-600': (new Date('{{ $exam->start_date->format('Y-m-d') }}') - new Date())/1000/60/60/24 <= 7 && (new Date('{{ $exam->start_date->format('Y-m-d') }}') - new Date())/1000/60/60/24 >= 3,
                                        'text-red-600': (new Date('{{ $exam->start_date->format('Y-m-d') }}') - new Date())/1000/60/60/24 < 3
                                     }" x-text="remaining"></div>
                                <a href="{{ route('exams.show', $exam->id) }}" class="inline-block mt-2 text-green-700 hover:underline text-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No upcoming exams</p>
                @endforelse
            </div>
        </x-card>
    </div>

    <!-- Recent Notices -->
    <x-card variant="bordered">
        <x-slot name="title">Recent Notices</x-slot>
        <div x-data="{ tab: 'all' }">
            <div class="flex gap-2 mb-4">
                <button @click="tab='all'" :class="tab==='all' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded">All</button>
                <button @click="tab='students'" :class="tab==='students' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded">Students</button>
                <button @click="tab='teachers'" :class="tab==='teachers' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded">Teachers</button>
                <button @click="tab='staff'" :class="tab==='staff' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded">Staff</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($recentNotices as $notice)
                    <div x-show="tab==='all' || tab==='{{ $notice->notice_for }}'" class="bg-white border rounded-lg p-4 animate-fade-in">
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
                        <a href="{{ route('notices.show', $notice->id) }}" class="mt-3 inline-block text-green-700 hover:underline text-sm">Read More</a>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4 col-span-full">No recent notices</p>
                @endforelse
            </div>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fee Collection Trend (Line)
    const feeCtx = document.getElementById('feeCollectionChart');
    if (feeCtx) {
        const feeChart = new Chart(feeCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($feeCollectionByMonth['labels'] ?? []),
                datasets: [{
                    label: 'Fee Collection',
                    data: @json($feeCollectionByMonth['data'] ?? []),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1000, easing: 'easeInOutQuart' },
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed.y ?? 0;
                                return 'Amount: ৳' + Number(val).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Student Distribution (Doughnut)
    const distCtx = document.getElementById('studentDistributionChart');
    if (distCtx) {
        const colors = [
            'rgba(34,197,94,0.9)',
            'rgba(59,130,246,0.9)',
            'rgba(139,92,246,0.9)',
            'rgba(245,158,11,0.9)',
            'rgba(239,68,68,0.9)',
            'rgba(16,185,129,0.9)'
        ];
        const distChart = new Chart(distCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json($departmentDistribution['labels'] ?? []),
                datasets: [{
                    label: 'Students',
                    data: @json($departmentDistribution['data'] ?? []),
                    backgroundColor: colors,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1000, easing: 'easeInOutQuart' },
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }

    // Attendance Overview (Bar) - This Month
    const attCtx = document.getElementById('attendanceChart');
    if (attCtx) {
        const present = {{ (int)($attendanceStats['present'] ?? 0) }};
        const absent  = {{ (int)($attendanceStats['absent'] ?? 0) }};
        const late    = {{ (int)($attendanceStats['late'] ?? 0) }};
        const attBar = new Chart(attCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    label: 'Attendance (count)',
                    data: [present, absent, late],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderRadius: 6,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1000, easing: 'easeInOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.parsed.y + ' entries';
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision:0 } }
                }
            }
        });
    }
});
</script>
@endpush
