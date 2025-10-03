@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Student Profile</h1>
        <div class="flex space-x-2">
            <a href="{{ route('students.edit', $student->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Edit
            </a>
            <a href="{{ route('students.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Back to List
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Student Profile Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-green-600 text-white p-4 flex items-center justify-center flex-col">
                @if($student->user->avatar)
                    <img class="w-24 h-24 rounded-full object-cover border-4 border-white" src="{{ asset('storage/' . $student->user->avatar) }}" alt="{{ $student->user->name }}">
                @else
                    <div class="w-24 h-24 rounded-full bg-green-700 flex items-center justify-center text-white text-3xl font-bold border-4 border-white">
                        {{ substr($student->user->name, 0, 1) }}
                    </div>
                @endif
                <h2 class="text-xl font-bold mt-2">{{ $student->user->name }}</h2>
                <p class="text-sm">{{ $student->admission_number }}</p>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Class</h3>
                    <p>{{ $student->class->name }} ({{ $student->class->department->name }})</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Roll Number</h3>
                    <p>{{ $student->roll_number ?? 'Not assigned' }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Status</h3>
                    <p>
                        @if($student->is_active)
                            <span class="bg-green-100 text-green-800 py-1 px-3 rounded-full text-xs">Active</span>
                        @else
                            <span class="bg-red-100 text-red-800 py-1 px-3 rounded-full text-xs">Inactive</span>
                        @endif
                    </p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Admission Date</h3>
                    <p>{{ $student->admission_date->format('d M, Y') }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Email</h3>
                    <p>{{ $student->user->email }}</p>
                </div>
                <div class="mb-4">
                    <h3 class="text-sm text-gray-500 font-semibold">Phone</h3>
                    <p>{{ $student->user->phone ?? 'Not provided' }}</p>
                </div>
            </div>
        </div>

        <!-- Student Details -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden md:col-span-2">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b">Personal Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 font-semibold">Date of Birth</h3>
                        <p>{{ $student->date_of_birth->format('d M, Y') }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 font-semibold">Gender</h3>
                        <p>{{ ucfirst($student->gender) }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 font-semibold">Blood Group</h3>
                        <p>{{ $student->blood_group ?? 'Not provided' }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 font-semibold">Address</h3>
                        <p>{{ $student->address }}</p>
                    </div>
                </div>
                
                <h2 class="text-xl font-bold text-gray-800 mb-4 pb-2 border-b mt-6">Guardian Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 font-semibold">Father's Name</h3>
                        <p>{{ $student->father_name }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 font-semibold">Mother's Name</h3>
                        <p>{{ $student->mother_name }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 font-semibold">Guardian's Phone</h3>
                        <p>{{ $student->guardian_phone }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="text-sm text-gray-500 font-semibold">Guardian's Email</h3>
                        <p>{{ $student->guardian_email ?? 'Not provided' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Academic Information -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden md:col-span-3">
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Academic Information</h2>
                
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Attendance Summary</h3>
                    <div class="bg-gray-100 rounded-lg p-4">
                        <div class="flex flex-wrap gap-4">
                            <div class="bg-white p-4 rounded-lg shadow flex-1">
                                <p class="text-sm text-gray-500">Present Days</p>
                                <p class="text-2xl font-bold text-green-600">{{ $presentCount }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow flex-1">
                                <p class="text-sm text-gray-500">Absent Days</p>
                                <p class="text-2xl font-bold text-red-600">{{ $absentCount }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow flex-1">
                                <p class="text-sm text-gray-500">Late Days</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ $lateCount }}</p>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow flex-1">
                                <p class="text-sm text-gray-500">Attendance Rate</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $attendanceRate }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Recent Results</h3>
                    @if(count($recentResults) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-700">
                                        <th class="py-2 px-4 border">Exam</th>
                                        <th class="py-2 px-4 border">Subject</th>
                                        <th class="py-2 px-4 border">Marks</th>
                                        <th class="py-2 px-4 border">Grade</th>
                                        <th class="py-2 px-4 border">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentResults as $result)
                                        <tr>
                                            <td class="py-2 px-4 border">{{ $result->exam->name }}</td>
                                            <td class="py-2 px-4 border">{{ $result->subject->name }}</td>
                                            <td class="py-2 px-4 border">{{ $result->marks_obtained }}/{{ $result->subject->full_mark }}</td>
                                            <td class="py-2 px-4 border">{{ $result->grade }}</td>
                                            <td class="py-2 px-4 border">{{ $result->remarks ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No recent results available</p>
                    @endif
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Fee Status</h3>
                    @if(count($pendingFees) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-700">
                                        <th class="py-2 px-4 border">Fee Type</th>
                                        <th class="py-2 px-4 border">Amount</th>
                                        <th class="py-2 px-4 border">Due Date</th>
                                        <th class="py-2 px-4 border">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingFees as $fee)
                                        <tr>
                                            <td class="py-2 px-4 border">{{ $fee->fee_type }}</td>
                                            <td class="py-2 px-4 border">{{ $fee->amount }}</td>
                                            <td class="py-2 px-4 border">{{ $fee->due_date->format('d M, Y') }}</td>
                                            <td class="py-2 px-4 border">
                                                @if($fee->status == 'paid')
                                                    <span class="bg-green-100 text-green-800 py-1 px-2 rounded-full text-xs">Paid</span>
                                                @elseif($fee->status == 'partial')
                                                    <span class="bg-yellow-100 text-yellow-800 py-1 px-2 rounded-full text-xs">Partial</span>
                                                @else
                                                    <span class="bg-red-100 text-red-800 py-1 px-2 rounded-full text-xs">Unpaid</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">No pending fees</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection