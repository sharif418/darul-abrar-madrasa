@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Register New Teacher</h1>
        <a href="{{ route('teachers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Back to List
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
        <form method="POST" action="{{ route('teachers.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User Information -->
                <div class="col-span-2">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">User Account Information</h2>
                </div>
                
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name *</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" value="{{ old('name') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address *</label>
                    <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" value="{{ old('email') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password *</label>
                    <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror" required>
                </div>
                
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                
                <div class="mb-4">
                    <label for="avatar" class="block text-gray-700 text-sm font-bold mb-2">Profile Photo</label>
                    <input type="file" name="avatar" id="avatar" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('avatar') border-red-500 @enderror">
                </div>
                
                <div class="mb-4">
                    <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number *</label>
                    <input type="text" name="phone" id="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('phone') border-red-500 @enderror" value="{{ old('phone') }}" required>
                </div>
                
                <!-- Teacher Information -->
                <div class="col-span-2 mt-4">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Teacher Information</h2>
                </div>
                
                <div class="mb-4">
                    <label for="department_id" class="block text-gray-700 text-sm font-bold mb-2">Department *</label>
                    <select name="department_id" id="department_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('department_id') border-red-500 @enderror" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="designation" class="block text-gray-700 text-sm font-bold mb-2">Designation *</label>
                    <input type="text" name="designation" id="designation" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('designation') border-red-500 @enderror" value="{{ old('designation') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="qualification" class="block text-gray-700 text-sm font-bold mb-2">Qualification *</label>
                    <input type="text" name="qualification" id="qualification" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('qualification') border-red-500 @enderror" value="{{ old('qualification') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="joining_date" class="block text-gray-700 text-sm font-bold mb-2">Joining Date *</label>
                    <input type="date" name="joining_date" id="joining_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('joining_date') border-red-500 @enderror" value="{{ old('joining_date') ?? date('Y-m-d') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address *</label>
                    <textarea name="address" id="address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('address') border-red-500 @enderror" required>{{ old('address') }}</textarea>
                </div>
                
                <div class="mb-4">
                    <label for="salary" class="block text-gray-700 text-sm font-bold mb-2">Salary *</label>
                    <input type="number" step="0.01" name="salary" id="salary" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('salary') border-red-500 @enderror" value="{{ old('salary') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="is_active" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                    <select name="is_active" id="is_active" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Register Teacher
                </button>
            </div>
        </form>
    </div>
</div>
@endsection