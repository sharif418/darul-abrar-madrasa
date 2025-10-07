@extends('layouts.app')

@section('title', 'Enroll Student - ' . $class->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="enrollStudentPage()">
    <!-- Breadcrumb -->
    <nav class="flex mb-6 text-sm text-gray-600" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('classes.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
                    </svg>
                    Classes
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707A1 1 0 118.707 5.293l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                              clip-rule="evenodd" />
                    </svg>
                    <a href="{{ route('classes.show', $class) }}" class="text-gray-500 hover:text-gray-700">{{ $class->name }}</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707A1 1 0 118.707 5.293l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                              clip-rule="evenodd" />
                    </svg>
                    <span class="text-gray-700 font-medium">Enroll Student</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200 text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200 text-red-800">
            {{ session('error') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="mb-4 rounded-md bg-yellow-50 p-4 border border-yellow-200 text-yellow-800">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Class info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Class Information</h2>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $class->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $class->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                    <div>
                        <div class="text-gray-500">Class Name</div>
                        <div class="font-medium">{{ $class->name }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Department</div>
                        <div class="font-medium">{{ optional($class->department)->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Section</div>
                        <div class="font-medium">{{ $class->section ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500">Capacity</div>
                        <div class="font-medium">
                            {{ $class->getStudentsCount() }} / {{ $class->capacity }}
                            @php $avail = $class->getAvailableSeats(); @endphp
                            <span class="ml-2 text-xs {{ $avail <= 0 ? 'text-red-600' : ($avail <= 5 ? 'text-yellow-600' : 'text-gray-500') }}">
                                {{ $avail <= 0 ? 'Full' : ($avail <= 5 ? 'Near capacity' : $avail . ' seats available') }}
                            </span>
                        </div>
                    </div>
                    @if($class->description)
                        <div class="md:col-span-2">
                            <div class="text-gray-500">Description</div>
                            <div class="font-medium">{{ $class->description }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 h-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Actions</h3>
                </div>
                <div class="px-6 py-5 space-y-3">
                    <a href="{{ route('classes.show', $class) }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel and go back
                    </a>
                    <div class="text-xs text-gray-500">
                        Select a student below and click "Enroll Selected Student" to enroll into this class.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment form -->
    <form method="POST" action="{{ route('classes.enroll-student', $class) }}" @submit="onSubmit">
        @csrf

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Select Student to Enroll</h2>
                <form method="GET" action="{{ route('classes.enroll-student.form', $class) }}" class="flex items-center space-x-2">
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search students..."
                               class="block w-64 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        <div class="absolute right-2 top-2.5 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1016.65 16.65z" />
                            </svg>
                        </div>
                    </div>
                    <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Search
                    </button>
                </form>
            </div>

            <div class="px-6 py-5">
                @error('student_id')
                    <div class="mb-3 rounded-md bg-red-50 p-3 border border-red-200 text-red-700 text-sm">
                        {{ $message }}
                    </div>
                @enderror

                @if($class->isFull())
                    <div class="mb-3 rounded-md bg-yellow-50 p-3 border border-yellow-200 text-yellow-800 text-sm">
                        This class is currently full. You must increase capacity or unenroll someone before enrolling a new student.
                    </div>
                @endif

                <div class="-mx-4 -my-2 overflow-x-auto">
                    <div class="inline-block min-w-full py-2 align-middle">
                        <div class="overflow-hidden border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Select</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Class</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admission No</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($availableStudents as $student)
                                        <tr x-show="matchesSearch('{{ strtolower($student->user->name) }}', '{{ strtolower($student->admission_number) }}', '{{ strtolower(optional($student->class)->name ?? '') }}', '{{ strtolower($student->user->email ?? '') }}', '{{ strtolower($student->user->phone ?? '') }}')">
                                            <td class="px-4 py-3">
                                                <input type="radio"
                                                       name="student_id"
                                                       value="{{ $student->id }}"
                                                       x-model="selectedId"
                                                       class="text-indigo-600 border-gray-300 focus:ring-indigo-500" />
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center">
                                                    <img src="{{ $student->user->avatar_url }}" alt="" class="h-8 w-8 rounded-full object-cover mr-3">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $student->user->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ $student->user->email ?? '—' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ optional($student->class)->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $student->admission_number }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $student->user->phone ?? '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                                No eligible students found to enroll.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-xs text-gray-500">
                                @if($availableStudents->total() > 0)
                                    Showing {{ $availableStudents->firstItem() }}–{{ $availableStudents->lastItem() }} of {{ $availableStudents->total() }} students
                                @else
                                    No students found
                                @endif
                            </p>
                            <div class="flex items-center gap-3">
                                <div>
                                    {{ $availableStudents->withQueryString()->links() }}
                                </div>
                                <div class="space-x-3">
                                    <a href="{{ route('classes.show', $class) }}"
                                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                        Cancel
                                    </a>
                                    <button type="submit"
                                            :disabled="!selectedId"
                                            :class="{'opacity-50 cursor-not-allowed': !selectedId}"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        Enroll Selected Student
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function enrollStudentPage() {
        return {
            search: '',
            selectedId: null,
            matchesSearch(name, admission, className, email, phone) {
                if (!this.search) return true;
                const q = this.search.toLowerCase();
                return name.includes(q)
                    || admission.includes(q)
                    || className.includes(q)
                    || email.includes(q)
                    || phone.includes(q);
            },
            onSubmit(e) {
                // prevent submit if no selection
                if (!this.selectedId) {
                    e.preventDefault();
                    alert('Please select a student to enroll.');
                }
            }
        };
    }
</script>
@endsection
