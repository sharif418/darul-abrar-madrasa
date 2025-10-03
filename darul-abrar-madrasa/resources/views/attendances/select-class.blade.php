@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Take Attendance</h1>
        <a href="{{ route('attendances.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Back to List
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Select Class</h2>
        <p class="text-gray-600 mb-6">Please select a class to take attendance for today ({{ now()->format('d M, Y') }}).</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($classes as $class)
                <a href="{{ route('attendances.create.class', $class->id) }}" class="block bg-gray-50 hover:bg-gray-100 p-4 rounded-lg border border-gray-200 transition-all">
                    <h3 class="font-semibold text-lg text-gray-800">{{ $class->name }}</h3>
                    <p class="text-gray-600">{{ $class->department->name }}</p>
                    <div class="mt-2 text-sm text-gray-500">
                        <span class="inline-block bg-blue-100 text-blue-800 py-1 px-2 rounded text-xs">
                            {{ $class->students->count() }} Students
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection