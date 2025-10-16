@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Weekly Timetable Grid</h1>
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

    <!-- Filters -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6 print:hidden">
        <form method="GET" action="{{ route('timetables.weekly-grid', $timetable) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">Filter by Class</label>
                <select name="class_id" id="class_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-2">Filter by Teacher</label>
                <select name="teacher_id" id="teacher_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                    <i class="fas fa-filter mr-2"></i>Apply
                </button>
                <a href="{{ route('timetables.weekly-grid', $timetable) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-150">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Weekly Grid -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-blue-600 to-blue-700">
                        <th class="border border-blue-500 px-4 py-3 text-left text-sm font-semibold text-white">Period / Day</th>
                        @foreach($grid['days'] as $day)
                            <th class="border border-blue-500 px-4 py-3 text-center text-sm font-semibold text-white capitalize">{{ $day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $allPeriods = collect($grid['periods'])->flatten(1)->unique('id')->sortBy('order');
                    @endphp
                    @foreach($allPeriods as $period)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-3 bg-gray-50 font-medium text-sm">
                                <div>{{ $period->name }}</div>
                                <div class="text-xs text-gray-500">{{ $period->getFormattedTimeRange() }}</div>
                            </td>
                            @foreach($grid['days'] as $day)
                                <td class="border border-gray-300 px-2 py-2 text-xs align-top">
                                    @if(isset($grid['grid'][$day][$period->id]))
                                        @php
                                            $cellData = $grid['grid'][$day][$period->id];
                                            $entries = is_array($cellData) ? collect($cellData)->flatten(1) : collect([$cellData])->filter();
                                        @endphp
                                        @foreach($entries as $entry)
                                            @if($entry)
                                                <div class="bg-blue-100 border border-blue-300 rounded p-2 mb-1">
                                                    <div class="font-semibold text-blue-900">{{ $entry->class->name ?? 'N/A' }}</div>
                                                    <div class="text-blue-700">{{ $entry->subject->name ?? 'N/A' }}</div>
                                                    @if($entry->teacher)
                                                        <div class="text-blue-600">{{ $entry->teacher->user->name }}</div>
                                                    @endif
                                                    @if($entry->room_number)
                                                        <div class="text-blue-500">{{ $entry->room_number }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="text-gray-400 text-center py-2">-</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .print\\:hidden {
        display: none !important;
    }
    table {
        font-size: 10px;
    }
    th, td {
        padding: 4px !important;
    }
}
</style>
@endsection
