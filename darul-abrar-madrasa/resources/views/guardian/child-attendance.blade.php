@extends('layouts.app')

@section('title', 'Child Attendance')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Breadcrumbs --}}
    <div class="mb-4 text-sm text-gray-600">
        <a href="{{ route('guardian.dashboard') }}" class="text-indigo-600 hover:underline">Guardian Dashboard</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">My Children</a>
        <span class="mx-2">/</span>
        <a href="{{ route('guardian.child.profile', $student) }}" class="text-indigo-600 hover:underline">Child Profile</a>
        <span class="mx-2">/</span>
        <span>Attendance</span>
    </div>

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Attendance - {{ $student->user->name ?? 'Student' }}</h1>
        <p class="text-gray-600 mt-1">Class: {{ $student->class->name ?? 'N/A' }} · Roll: {{ $student->roll_number ?? 'N/A' }}</p>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
    @endif

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-stat-card title="Attendance Rate" value="{{ number_format((float)($attendancePercentage ?? 0), 2) }}%" icon="chart-bar" color="green" />
        <x-stat-card title="Total Records" value="{{ number_format(count($records ?? [])) }}" icon="clipboard-document" color="blue" />
        @php
            $present = collect($records ?? [])->where('status', 'present')->count();
            $absent = collect($records ?? [])->where('status', 'absent')->count();
        @endphp
        <x-stat-card title="Present/Absent" value="{{ $present }} / {{ $absent }}" icon="user-group" color="indigo" />
    </div>

    {{-- Filters --}}
    <x-card class="mb-6">
        <form method="GET" action="{{ route('guardian.child.attendance', $student) }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <x-label for="from" value="From" />
                <x-input id="from" name="from" type="date" value="{{ request('from') }}" class="mt-1 w-full" />
            </div>
            <div>
                <x-label for="to" value="To" />
                <x-input id="to" name="to" type="date" value="{{ request('to') }}" class="mt-1 w-full" />
            </div>
            <div>
                <x-label for="status" value="Status" />
                <x-select id="status" name="status" class="mt-1 w-full">
                    @php $status = request('status'); @endphp
                    <option value="" {{ $status === null || $status === '' ? 'selected' : '' }}>All</option>
                    <option value="present" {{ $status === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ $status === 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="late" {{ $status === 'late' ? 'selected' : '' }}>Late</option>
                    <option value="excused" {{ $status === 'excused' ? 'selected' : '' }}>Excused</option>
                </x-select>
            </div>
            <div class="flex items-end">
                <x-button type="submit" class="w-full">Filter</x-button>
            </div>
            <div class="flex items-end">
                <a href="{{ route('guardian.child.attendance', $student) }}" class="inline-flex items-center justify-center w-full px-4 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </x-card>

    {{-- Attendance Table --}}
    <x-card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Attendance Records</h2>
            <div class="flex gap-3">
                <a href="#" class="inline-flex items-center px-4 py-2 bg-white border rounded-md text-gray-700 hover:bg-gray-50">Download Report</a>
            </div>
        </div>
        <x-table>
            <x-slot name="head">
                <tr>
                    <x-table.th>Date</x-table.th>
                    <x-table.th>Status</x-table.th>
                    <x-table.th>Remarks</x-table.th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse(($records ?? []) as $rec)
                    <tr class="border-b">
                        <x-table.td>{{ \Carbon\Carbon::parse($rec->date)->format('Y-m-d') }}</x-table.td>
                        <x-table.td>
                            @php $st = strtolower($rec->status ?? ''); @endphp
                            @if($st === 'present')
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Present</span>
                            @elseif($st === 'absent')
                                <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">Absent</span>
                            @elseif($st === 'late')
                                <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">Late</span>
                            @elseif($st === 'excused')
                                <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">Excused</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">{{ ucfirst($rec->status ?? 'N/A') }}</span>
                            @endif
                        </x-table.td>
                        <x-table.td>{{ $rec->remarks ?? '-' }}</x-table.td>
                    </tr>
                @empty
                    <tr>
                        <x-table.td colspan="3">
                            <div class="text-center text-gray-500 py-8">No attendance records found for the selected filters.</div>
                        </x-table.td>
                    </tr>
                @endforelse
            </x-slot>
        </x-table>
    </x-card>

    {{-- Footer links --}}
    <div class="mt-6 text-sm text-gray-600">
        <a href="{{ route('guardian.child.profile', $student) }}" class="text-indigo-600 hover:underline">Back to Profile</a>
        <span class="mx-2">&middot;</span>
        <a href="{{ route('guardian.children') }}" class="text-indigo-600 hover:underline">Back to Children</a>
    </div>
</div>
@endsection
