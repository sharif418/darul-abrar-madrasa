@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Enter Exam Results</h1>
        <a href="{{ route('exams.show', $exam->id) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Back to Exam
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Please fix the following errors:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Result Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <span class="text-gray-600 font-semibold">Exam:</span> 
                    <span>{{ $exam->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Class:</span> 
                    <span>{{ $class->name }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Subject:</span> 
                    <span>{{ $subject->name }} ({{ $subject->code }})</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Full Mark:</span> 
                    <span>{{ $subject->full_mark }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Pass Mark:</span> 
                    <span>{{ $subject->pass_mark }}</span>
                </div>
                <div>
                    <span class="text-gray-600 font-semibold">Total Students:</span> 
                    <span>{{ count($students) }}</span>
                </div>
            </div>
        </div>

        <!-- Subject Selection -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Select Subject</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($subjects as $sub)
                    <a href="{{ route('results.create.bulk', ['exam_id' => $exam->id, 'class_id' => $class->id, 'subject_id' => $sub->id]) }}" 
                       class="px-4 py-2 rounded {{ $sub->id == $subject->id ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                        {{ $sub->name }}
                    </a>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('results.store.bulk') }}">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Roll No.</th>
                            <th class="py-3 px-6 text-left">Student Name</th>
                            <th class="py-3 px-6 text-center">Marks (out of {{ $subject->full_mark }})</th>
                            <th class="py-3 px-6 text-center">Grade</th>
                            <th class="py-3 px-6 text-left">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        @forelse($students as $student)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left">{{ $student->roll_number ?? 'N/A' }}</td>
                                <td class="py-3 px-6 text-left">
                                    <div class="flex items-center">
                                        @if($student->user->avatar)
                                            <div class="mr-2">
                                                <img class="w-8 h-8 rounded-full" src="{{ asset('storage/' . $student->user->avatar) }}" alt="{{ $student->user->name }}">
                                            </div>
                                        @endif
                                        <span>{{ $student->user->name }}</span>
                                    </div>
                                    <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <input type="number" name="marks[{{ $student->id }}]" min="0" max="{{ $subject->full_mark }}" step="0.01" class="shadow appearance-none border rounded w-24 py-2 px-3 text-center text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('marks.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->marks_obtained : '') }}" required>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <select name="grade[{{ $student->id }}]" class="shadow appearance-none border rounded w-24 py-2 px-3 text-center text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <option value="">Auto</option>
                                        <option value="A+" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'A' ? 'selected' : '' }}>A</option>
                                        <option value="A-" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'B' ? 'selected' : '' }}>B</option>
                                        <option value="B-" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="C+" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'C+' ? 'selected' : '' }}>C+</option>
                                        <option value="C" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'C' ? 'selected' : '' }}>C</option>
                                        <option value="C-" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'C-' ? 'selected' : '' }}>C-</option>
                                        <option value="D" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'D' ? 'selected' : '' }}>D</option>
                                        <option value="F" {{ old('grade.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->grade : '') == 'F' ? 'selected' : '' }}>F</option>
                                    </select>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <input type="text" name="remarks[{{ $student->id }}]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('remarks.' . $student->id, isset($existingResults[$student->id]) ? $existingResults[$student->id]->remarks : '') }}" placeholder="Optional remarks">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 px-6 text-center text-gray-500">No students found in this class</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(count($students) > 0)
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Save Results
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection