@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Teaching Schedule for {{ $teacher->user->name }}</h1>
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

    <!-- Teacher Info & Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <p class="text-sm text-gray-500">Teacher</p>
            <p class="text-lg font-semibold text-gray-900">{{ $teacher->user->name }}</p>
            <p class="text-sm text-gray-600 mt-1">{{ $teacher->designation ?? 'Teacher' }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-blue-100 text-sm">Total Periods/Week</p>
            <p class="text-3xl font-bold mt-2">{{ $schedule['stats']['total_periods'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-green-100 text-sm">Classes Taught</p>
            <p class="text-3xl font-bold mt-2">{{ $schedule['stats']['classes_taught'] }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-purple-100 text-sm">Subjects Taught</p>
            <p class="text-3xl font-bold mt-2">{{ $schedule['stats']['subjects_taught'] }}</p>
        </div>
    </div>

    <!-- Weekly Schedule Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-purple-600 to-purple-700">
                        <th class="border border-purple-500 px-4 py-3 text-left text-sm font-semibold text-white">Period</th>
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <th class="border border-purple-500 px-4 py-3 text-center text-sm font-semibold text-white">{{ $day }}</th>
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
                                        <div class="bg-purple-100 border border-purple-300 rounded p-2">
                                            <div class="font-bold text-purple-900">{{ $dayEntry->class->name ?? 'N/A' }}</div>
                                            <div class="text-purple-700 mt-1">{{ $dayEntry->subject->name ?? 'N/A' }}</div>
                                            @if($dayEntry->room_number)
                                                <div class="text-purple-600 mt-1">
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

    <!-- Class Summary -->
    @if(isset($schedule['schedule']) && count($schedule['schedule']) > 0)
        <div class="mt-6 bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Classes Taught</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $classSummary = collect($schedule['schedule'])->flatten(1)->groupBy('class_id');
                @endphp
                @foreach($classSummary as $classId => $entries)
                    @php
                        $firstEntry = $entries->first();
                    @endphp
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h3 class="font-semibold text-purple-900">{{ $firstEntry->class->name ?? 'N/A' }}</h3>
                        <p class="text-sm text-purple-700 mt-1">Subject: {{ $firstEntry->subject->name ?? 'N/A' }}</p>
                        <p class="text-sm text-purple-600 mt-1">Periods/Week: {{ $entries->count() }}</p>
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
