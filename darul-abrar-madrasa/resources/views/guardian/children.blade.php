@extends('layouts.app')

@section('content')
<div class="py-6">
  <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
      <h1 class="text-2xl font-bold text-gray-800 mb-4">My Children</h1>

      @if(session('success'))
        <x-alert type="success" :message="session('success')" />
      @endif
      @if(session('error'))
        <x-alert type="error" :message="session('error')" />
      @endif

      @php $students = $students ?? collect(); @endphp
      @if($students->isEmpty())
        <div class="text-gray-600">No linked children found.</div>
      @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($students as $student)
            <x-card>
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-lg font-semibold text-gray-800">
                    {{ optional($student->user)->name ?? 'Student #'.$student->id }}
                  </div>
                  <div class="text-sm text-gray-500">
                    Class: {{ optional($student->class)->name ?? 'N/A' }}
                  </div>
                </div>
                <div class="text-sm text-gray-600">Roll: {{ $student->roll_number ?? '-' }}</div>
              </div>

              <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="p-3 bg-gray-50 rounded border">
                  <div class="text-gray-500">Attendance%</div>
                  @php $rate = method_exists($student,'getAttendanceRate') ? (float)$student->getAttendanceRate() : 0; @endphp
                  <div class="font-semibold text-gray-800">{{ number_format($rate, 2) }}%</div>
                </div>
                <div class="p-3 bg-gray-50 rounded border">
                  <div class="text-gray-500">Pending Fees</div>
                  <div class="font-semibold text-gray-800">
                    ৳ {{ number_format((float)($student->getPendingFeesAmount() ?? 0), 2) }}
                  </div>
                </div>
              </div>

              <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('guardian.child.profile', $student) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Profile</a>
                <a href="{{ route('guardian.child.attendance', $student) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">Attendance</a>
                <a href="{{ route('guardian.child.results', $student) }}" class="inline-flex items-center px-3 py-1.5 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">Results</a>
                <a href="{{ route('guardian.child.fees', $student) }}" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 text-sm">Fees</a>
                <a href="{{ route('guardian.child.materials', $student) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-700 text-white rounded hover:bg-gray-800 text-sm">Study Materials</a>
              </div>
            </x-card>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
