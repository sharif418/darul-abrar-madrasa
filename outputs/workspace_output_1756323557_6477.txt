@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">My Results</h1>
    </div>

    <!-- Filter -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <form action="{{ route('my.results') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="w-full md:w-auto">
                <label for="exam_id" class="block text-gray-700 text-sm font-bold mb-2">Exam</label>
                <select name="exam_id" id="exam_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">All Exams</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                            {{ $exam->name }} ({{ $exam->start_date->format('M Y') }})
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
                <a href="{{ route('my.results') }}" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($selectedExam)
        <!-- Result Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="bg-blue-600 text-white p-4">
                <h2 class="text-xl font-bold">{{ $selectedExam->name }}</h2>
                <p class="text-sm">{{ $selectedExam->start_date->format('d M, Y') }} to {{ $selectedExam->end_date->format('d M, Y') }}</p>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Student Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-gray-600 font-semibold">Name:</span> 
                            <span>{{ $student->user->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-semibold">Class:</span> 
                            <span>{{ $student->class->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-semibold">Roll Number:</span> 
                            <span>{{ $student->roll_number ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-semibold">Admission Number:</span> 
                            <span>{{ $student->admission_number }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Subject Marks</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700">
                                    <th class="py-2 px-4 border">Subject</th>
                                    <th class="py-2 px-4 border">Full Mark</th>
                                    <th class="py-2 px-4 border">Pass Mark</th>
                                    <th class="py-2 px-4 border">Obtained Mark</th>
                                    <th class="py-2 px-4 border">Grade</th>
                                    <th class="py-2 px-4 border">Status</th>
                                    <th class="py-2 px-4 border">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalFullMarks = 0;
                                    $totalObtainedMarks = 0;
                                    $failedSubjects = 0;
                                @endphp
                                
                                @forelse($examResults as $result)
                                    @php
                                        $totalFullMarks += $result->subject->full_mark;
                                        $totalObtainedMarks += $result->marks_obtained;
                                        if($result->marks_obtained < $result->subject->pass_mark) {
                                            $failedSubjects++;
                                        }
                                    @endphp
                                    <tr>
                                        <td class="py-2 px-4 border">{{ $result->subject->name }}</td>
                                        <td class="py-2 px-4 border text-center">{{ $result->subject->full_mark }}</td>
                                        <td class="py-2 px-4 border text-center">{{ $result->subject->pass_mark }}</td>
                                        <td class="py-2 px-4 border text-center font-semibold {{ $result->marks_obtained >= $result->subject->pass_mark ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $result->marks_obtained }}
                                        </td>
                                        <td class="py-2 px-4 border text-center">{{ $result->grade }}</td>
                                        <td class="py-2 px-4 border text-center">
                                            @if($result->marks_obtained >= $result->subject->pass_mark)
                                                <span class="bg-green-100 text-green-800 py-1 px-2 rounded-full text-xs">Passed</span>
                                            @else
                                                <span class="bg-red-100 text-red-800 py-1 px-2 rounded-full text-xs">Failed</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border">{{ $result->remarks ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-4 px-4 border text-center text-gray-500">No results available for this exam</td>
                                    </tr>
                                @endforelse
                                
                                @if(count($examResults) > 0)
                                    <tr class="bg-gray-50 font-semibold">
                                        <td class="py-2 px-4 border">Total</td>
                                        <td class="py-2 px-4 border text-center">{{ $totalFullMarks }}</td>
                                        <td class="py-2 px-4 border"></td>
                                        <td class="py-2 px-4 border text-center">{{ $totalObtainedMarks }}</td>
                                        <td class="py-2 px-4 border"></td>
                                        <td class="py-2 px-4 border"></td>
                                        <td class="py-2 px-4 border"></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if(count($examResults) > 0)
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Result Summary</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-blue-100 p-4 rounded-lg">
                                <div class="text-blue-800 font-semibold">Total Marks</div>
                                <div class="text-2xl font-bold">{{ $totalObtainedMarks }}/{{ $totalFullMarks }}</div>
                            </div>
                            <div class="bg-green-100 p-4 rounded-lg">
                                <div class="text-green-800 font-semibold">Percentage</div>
                                <div class="text-2xl font-bold">{{ round(($totalObtainedMarks / $totalFullMarks) * 100, 2) }}%</div>
                            </div>
                            <div class="bg-yellow-100 p-4 rounded-lg">
                                <div class="text-yellow-800 font-semibold">Result</div>
                                <div class="text-2xl font-bold">{{ $failedSubjects > 0 ? 'FAILED' : 'PASSED' }}</div>
                            </div>
                            <div class="bg-purple-100 p-4 rounded-lg">
                                <div class="text-purple-800 font-semibold">Position</div>
                                <div class="text-2xl font-bold">{{ $position }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('results.download', $selectedExam->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Download Result
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- All Results List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">All Results</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Exam</th>
                            <th class="py-3 px-6 text-left">Date</th>
                            <th class="py-3 px-6 text-center">Total Subjects</th>
                            <th class="py-3 px-6 text-center">Total Marks</th>
                            <th class="py-3 px-6 text-center">Percentage</th>
                            <th class="py-3 px-6 text-center">Result</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        @forelse($examSummaries as $examId => $summary)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left">{{ $summary['name'] }}</td>
                                <td class="py-3 px-6 text-left">{{ $summary['date'] }}</td>
                                <td class="py-3 px-6 text-center">{{ $summary['subjects'] }}</td>
                                <td class="py-3 px-6 text-center">{{ $summary['obtained'] }}/{{ $summary['full'] }}</td>
                                <td class="py-3 px-6 text-center">{{ $summary['percentage'] }}%</td>
                                <td class="py-3 px-6 text-center">
                                    @if($summary['failed'] > 0)
                                        <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs">Failed</span>
                                    @else
                                        <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Passed</span>
                                    @endif
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <a href="{{ route('my.results', ['exam_id' => $examId]) }}" class="bg-blue-500 hover:bg-blue-700 text-white text-xs py-1 px-2 rounded focus:outline-none focus:shadow-outline">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 px-6 text-center text-gray-500">No results found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection