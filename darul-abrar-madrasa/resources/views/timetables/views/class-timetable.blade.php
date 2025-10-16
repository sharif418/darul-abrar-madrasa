@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Timetable for {{ $class->name }}</h1>
            <p class="text-gray-600 mt-1">{{ $timetable->name }}</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                <i class="fas fa-print mr-2"></i>Print
            </button>
            <a href="{{ route('timetables.show', $timetable) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <!-- Class Info Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Class</p>
                <p class="text-lg font-semibold text-gray-900">{{ $class->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Department</p>
                <p class="text-lg font-semibold text-gray-900">{{ $class->department->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Class Teacher</p>
                <p class="text-lg font-semibold text-gray-900">{{ $class->classTeacher->user->name ?? 'Not Assigned' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Periods/Week</p>
                <p class="text-lg font-semibold text-blue-600">{{ $schedule['totalPeriods'] }}</p>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-green-600 to-green-700">
                        <th class="border border-green-500 px-4 py-3 text-left text-sm font-semibold text-white">Period</th>
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <th class="border border-green-500 px-4 py-3 text-center text-sm font-semibold text-white">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allPeriods = collect($schedule['schedule'])->flatten(1)->unique('period_id')->sortBy(function($entry) {
                            return $entry->period->order ?? 999;
                        });
                    @endphp
                    @foreach($allPeriods as $periodEntry)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-3 bg-gray-50 font-medium text-sm">
                                <div>{{ $periodEntry->period->name }}</div>
                                <div class="text-xs text-gray-500">{{ $periodEntry->period->getFormattedTimeRange() }}</div>
                            </td>
                            @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                <td class="border border-gray-300 px-2 py-2 text-xs">
                                    @php
                                        $dayEntry = isset($schedule['schedule'][$day]) 
                                            ? collect($schedule['schedule'][$day])->firstWhere('period_id', $periodEntry->period_id)
                                            : null;
                                    @endphp
                                    @if($dayEntry)
                                        <div class="bg-green-100 border border-green-300 rounded p-2">
                                            <div class="font-bold text-green-900">{{ $dayEntry->subject->name ?? 'N/A' }}</div>
                                            @if($dayEntry->teacher)
                                                <div class="text-green-700 mt-1">{{ $dayEntry->teacher->user->name }}</div>
                                            @endif
                                            @if($dayEntry->room_number)
                                                <div class="text-green-600 mt-1">
                                                    <i class="fas fa-door-open mr-1"></i>{{ $dayEntry->room_number }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-gray-400 text-center py-2">Free</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Subject Summary -->
    @if(isset($schedule['schedule']) && count($schedule['schedule']) > 0)
        <div class="mt-6 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Subject Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $subjectSummary = collect($schedule['schedule'])->flatten(1)->groupBy('subject_id');
                @endphp
                @foreach($subjectSummary as $subjectId => $entries)
                    @php
                        $firstEntry = $entries->first();
                    @endphp
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-900">{{ $firstEntry->subject->name ?? 'N/A' }}</h3>
                        <p class="text-sm text-green-700 mt-1">Teacher: {{ $firstEntry->teacher->user->name ?? 'Not Assigned' }}</p>
                        <p class="text-sm text-green-600 mt-1">Periods/Week: {{ $entries->count() }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<style>
@media print {
    .print\\:hidden, button, a {
        display: none !important;
    }
    table {
        font-size: 10px;
    }
}
</style>
@endsection
