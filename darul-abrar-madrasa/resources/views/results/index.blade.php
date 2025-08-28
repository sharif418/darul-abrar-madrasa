@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Exam Results</h1>
        @if(isset($exam))
            <a href="{{ route('exams.show', $exam->id) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Back to Exam
            </a>
        @endif
    </div>

    <!-- Search and Filter -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('results.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="w-full md:w-auto">
                <label for="exam_id" class="block text-gray-700 text-sm font-bold mb-2">Exam</label>
                <select name="exam_id" id="exam_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Exams</option>
                    @foreach($exams as $examOption)
                        <option value="{{ $examOption->id }}" {{ request('exam_id') == $examOption->id ? 'selected' : '' }}>
                            {{ $examOption->name }} ({{ $examOption->class->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <label for="class_id" class="block text-gray-700 text-sm font-bold mb-2">Class</label>
                <select name="class_id" id="class_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ $class->department->name }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <label for="subject_id" class="block text-gray-700 text-sm font-bold mb-2">Subject</label>
                <select name="subject_id" id="subject_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }} ({{ $subject->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto">
                <label for="student_id" class="block text-gray-700 text-sm font-bold mb-2">Student</label>
                <select name="student_id" id="student_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Students</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }} ({{ $student->admission_number }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-auto flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Filter
                </button>
            </div>
            
            <div class="w-full md:w-auto flex items-end">
                <a href="{{ route('results.index') }}" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    @if(isset($exam) && isset($subject))
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Results Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-blue-100 p-4 rounded-lg">
                    <div class="text-blue-800 font-semibold">Total Students</div>
                    <div class="text-2xl font-bold">{{ $totalStudents }}</div>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <div class="text-green-800 font-semibold">Passed</div>
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
                <div class="bg-purple-100 p-4 rounded-lg">
                    <div class="text-purple-800 font-semibold">Average Mark</div>
                    <div class="text-2xl font-bold">{{ $averageMark }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Results List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">ID</th>
                        <th class="py-3 px-6 text-left">Student</th>
                        <th class="py-3 px-6 text-left">Exam</th>
                        <th class="py-3 px-6 text-left">Subject</th>
                        <th class="py-3 px-6 text-center">Marks</th>
                        <th class="py-3 px-6 text-center">Grade</th>
                        <th class="py-3 px-6 text-left">Remarks</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($results as $result)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 text-left">{{ $result->id }}</td>
                            <td class="py-3 px-6 text-left">
                                <div class="flex items-center">
                                    @if($result->student->user->avatar)
                                        <div class="mr-2">
                                            <img class="w-8 h-8 rounded-full" src="{{ asset('storage/' . $result->student->user->avatar) }}" alt="{{ $result->student->user->name }}">
                                        </div>
                                    @endif
                                    <span>{{ $result->student->user->name }}</span>
                                </div>
                                <div class="text-xs text-gray-500">{{ $result->student->admission_number }}</div>
                            </td>
                            <td class="py-3 px-6 text-left">{{ $result->exam->name }}</td>
                            <td class="py-3 px-6 text-left">{{ $result->subject->name }}</td>
                            <td class="py-3 px-6 text-center">
                                <span class="{{ $result->marks_obtained >= $result->subject->pass_mark ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                    {{ $result->marks_obtained }}
                                </span>
                                <span class="text-xs text-gray-500">/{{ $result->subject->full_mark }}</span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <span class="py-1 px-3 rounded-full text-xs {{ $result->marks_obtained >= $result->subject->pass_mark ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $result->grade }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-left">{{ $result->remarks ?? '-' }}</td>
                            <td class="py-3 px-6 text-center">
                                <div class="flex item-center justify-center">
                                    @if(!$result->exam->is_result_published)
                                        <a href="{{ route('results.edit', $result->id) }}" class="w-4 mr-2 transform hover:text-yellow-500 hover:scale-110 transition-all">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('results.destroy', $result->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this result?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-4 mr-2 transform hover:text-red-500 hover:scale-110 transition-all">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">Published</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 px-6 text-center text-gray-500">No results found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4">
            {{ $results->links() }}
        </div>
    </div>
</div>
@endsection