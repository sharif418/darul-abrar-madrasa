@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Exam Details</h1>
        <div class="flex space-x-2">
            <a href="{{ route('exams.edit', $exam->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Edit
            </a>
            <a href="{{ route('exams.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Exam Details Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 text-white p-4">
                <h2 class="text-xl font-bold">{{ $exam->name }}</h2>
                <p class="text-sm">{{ $exam->class->name }} ({{ $exam->class->department->name }})</p>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Schedule</h3>
                    <p>{{ $exam->start_date->format('d M, Y') }} to {{ $exam->end_date->format('d M, Y') }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Status</h3>
                    <p>
                        @php
                            $now = now();
                            $status = 'upcoming';
                            $statusClass = 'bg-blue-100 text-blue-800';
                            $statusText = 'Upcoming';
                            
                            if ($now->between($exam->start_date, $exam->end_date)) {
                                $status = 'ongoing';
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                $statusText = 'Ongoing';
                            } elseif ($now->gt($exam->end_date)) {
                                $status = 'completed';
                                $statusClass = 'bg-green-100 text-green-800';
                                $statusText = 'Completed';
                            }
                        @endphp
                        
                        <span class="{{ $statusClass }} py-1 px-3 rounded-full text-xs">{{ $statusText }}</span>
                        
                        @if(!$exam->is_active)
                            <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs ml-1">Inactive</span>
                        @endif
                    </p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Results</h3>
                    <p>
                        @if($exam->is_result_published)
                            <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Published</span>
                        @else
                            <span class="bg-gray-100 text-gray-800 py-1 px-3 rounded-full text-xs">Not Published</span>
                        @endif
                    </p>
                </div>
                @if($exam->description)
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 font-semibold">Description</h3>
                        <p class="text-gray-700">{{ $exam->description }}</p>
                    </div>
                @endif
                
                @php
                    // Determine if all subjects have complete results for all students
                    $studentsCount = $exam->class->students()->count();
                    $totalSubjects = $subjects->count();
                    $completedSubjects = 0;
                    foreach ($subjects as $s) {
                        $c = $exam->results()->where('subject_id', $s->id)->count();
                        if ($c >= $studentsCount && $studentsCount > 0) {
                            $completedSubjects++;
                        }
                    }
                    $allComplete = ($totalSubjects > 0) && ($completedSubjects === $totalSubjects);
                @endphp

                @if($status === 'completed' && !$exam->is_result_published && $allComplete)
                    <div class="mt-6">
                        <form action="{{ route('exams.publish-results', $exam->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to publish the results? This action cannot be undone.');">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Publish Results
                            </button>
                        </form>
                    </div>
                @elseif($status === 'completed' && !$exam->is_result_published && !$allComplete)
                    <div class="mt-6">
                        <button type="button" class="w-full bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded cursor-not-allowed" title="Results are incomplete across subjects">
                            Publish Results (Disabled - Incomplete)
                        </button>
                        <p class="mt-2 text-xs text-gray-600">
                            Results entered for {{ $completedSubjects }}/{{ $totalSubjects }} subjects. All subjects must be complete before publishing.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Subjects and Schedule -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden md:col-span-2">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Subjects and Schedule</h2>

                @php
                    $studentsCount = $exam->class->students()->count();
                    $totalSubjects = $subjects->count();
                    $completedSubjects = 0;
                    foreach ($subjects as $s) {
                        $c = $exam->results()->where('subject_id', $s->id)->count();
                        if ($c >= $studentsCount && $studentsCount > 0) {
                            $completedSubjects++;
                        }
                    }
                    $completionPercent = $totalSubjects > 0 ? round(($completedSubjects / $totalSubjects) * 100) : 0;
                @endphp

                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <div class="text-sm text-gray-700">
                            Results completion: {{ $completedSubjects }}/{{ $totalSubjects }} subjects ({{ $completionPercent }}%)
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $completionPercent }}%;"></div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="py-2 px-4 border">Subject</th>
                                <th class="py-2 px-4 border">Code</th>
                                <th class="py-2 px-4 border">Teacher</th>
                                <th class="py-2 px-4 border">Full Mark</th>
                                <th class="py-2 px-4 border">Pass Mark</th>
                                <th class="py-2 px-4 border">Status</th>
                                <th class="py-2 px-4 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subjects as $subject)
                                @php
                                    $resCount = $exam->results()->where('subject_id', $subject->id)->count();
                                    if ($resCount === 0) {
                                        $statusLabel = 'None';
                                        $statusClass = 'bg-red-100 text-red-800';
                                    } elseif ($resCount < $studentsCount) {
                                        $statusLabel = 'Partial';
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                    } else {
                                        $statusLabel = 'Complete';
                                        $statusClass = 'bg-green-100 text-green-800';
                                    }
                                @endphp
                                <tr>
                                    <td class="py-2 px-4 border">{{ $subject->name }}</td>
                                    <td class="py-2 px-4 border">{{ $subject->code }}</td>
                                    <td class="py-2 px-4 border">{{ $subject->teacher ? $subject->teacher->user->name : 'Not Assigned' }}</td>
                                    <td class="py-2 px-4 border">{{ $subject->full_mark }}</td>
                                    <td class="py-2 px-4 border">{{ $subject->pass_mark }}</td>
                                    <td class="py-2 px-4 border">
                                        <span class="px-2 py-1 rounded-full text-xs {{ $statusClass }}">
                                            {{ $statusLabel }} ({{ $resCount }}/{{ $studentsCount }})
                                        </span>
                                    </td>
                                    <td class="py-2 px-4 border">
                                        @if($status === 'completed' && !$exam->is_result_published)
                                            <a href="{{ route('results.create.bulk', ['exam_id' => $exam->id, 'class_id' => $exam->class_id, 'subject_id' => $subject->id]) }}" class="text-blue-600 hover:text-blue-800">
                                                Enter Results
                                            </a>
                                        @elseif($exam->is_result_published)
                                            <a href="{{ route('results.index', ['exam_id' => $exam->id, 'subject_id' => $subject->id]) }}" class="text-green-600 hover:text-green-800">
                                                View Results
                                            </a>
                                        @else
                                            <span class="text-gray-400">Not Available</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 px-4 border text-center text-gray-500">No subjects found for this class</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Results Summary -->
        @if($exam->is_result_published)
            <div class="bg-white shadow-md rounded-lg overflow-hidden md:col-span-3">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Results Summary</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-green-100 p-4 rounded-lg">
                            <div class="text-green-800 font-semibold">Total Students</div>
                            <div class="text-2xl font-bold">{{ $totalStudents }}</div>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <div class="text-blue-800 font-semibold">Passed</div>
                            <div class="text-2xl font-bold">{{ $passedStudents }}</div>
                        </div>
                        <div class="bg-red-100 p-4 rounded-lg">
                            <div class="text-red-800 font-semibold">Failed</div>
                            <div class="text-2xl font-bold">{{ $failedStudents }}</div>
                        </div>
                        <div class="bg-yellow-100 p-4 rounded-lg">
                            <div class="text-yellow-800 font-semibold">Pass Rate</div>
                            <div class="text-2xl font-bold">{{ $passRate }}%</div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <a href="{{ route('results.index', ['exam_id' => $exam->id]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            View All Results
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection