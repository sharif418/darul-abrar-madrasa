<div>
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="class_id" class="block text-gray-700 text-sm font-bold mb-2">Class</label>
                <select wire:model.live="class_id" id="class_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="exam_id" class="block text-gray-700 text-sm font-bold mb-2">Exam</label>
                <select wire:model.live="exam_id" id="exam_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" {{ !$class_id ? 'disabled' : '' }}>
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->name }} ({{ $exam->start_date->format('M Y') }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    
    @if(session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg wire:click="$set('showSuccessAlert', false)" class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </span>
        </div>
    @endif
    
    @if($showTable)
        <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Marks Entry Form</h3>
                <p class="text-sm text-gray-600">Enter marks for each student and subject</p>
            </div>
            
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full bg-white border">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="py-3 px-4 border text-left">Student</th>
                            <th class="py-3 px-4 border text-left">Roll No.</th>
                            @foreach($subjects as $subject)
                                <th class="py-3 px-4 border text-center">
                                    <div>{{ $subject->name }}</div>
                                    <div class="text-xs text-gray-500">FM: {{ $subject->full_mark }} | PM: {{ $subject->pass_mark }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 border">{{ $student->user->name }}</td>
                                <td class="py-2 px-4 border">{{ $student->roll_number }}</td>
                                @foreach($subjects as $subject)
                                    <td class="py-2 px-4 border text-center">
                                        <input 
                                            type="number" 
                                            wire:model="marks.{{ $student->id }}.{{ $subject->id }}" 
                                            class="shadow appearance-none border rounded w-20 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-center"
                                            min="0"
                                            max="{{ $subject->full_mark }}"
                                            placeholder="0"
                                        >
                                        @if(!empty($marks[$student->id][$subject->id]))
                                            @php
                                                $gradeInfo = $this->calculateGrade($marks[$student->id][$subject->id], $subject->id);
                                            @endphp
                                            <div class="mt-1 text-xs {{ $gradeInfo['is_passed'] ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                                {{ $gradeInfo['grade'] }} ({{ $gradeInfo['gpa_point'] }})
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($subjects) + 2 }}" class="py-4 px-4 border text-center text-gray-500">
                                    No students found in this class.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button 
                    wire:click="saveMarks" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    {{ count($students) == 0 ? 'disabled' : '' }}
                >
                    Save All Marks
                </button>
            </div>
        </div>
    @elseif($class_id && $exam_id)
        <div class="bg-white shadow-md rounded-lg p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">No Students Found</h3>
            <p class="text-gray-600">There are no students enrolled in this class or the selected exam is not configured properly.</p>
        </div>
    @elseif($class_id)
        <div class="bg-white shadow-md rounded-lg p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Select an Exam</h3>
            <p class="text-gray-600">Please select an exam to continue with marks entry.</p>
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg p-8 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Select a Class</h3>
            <p class="text-gray-600">Please select a class to begin the marks entry process.</p>
        </div>
    @endif
</div>