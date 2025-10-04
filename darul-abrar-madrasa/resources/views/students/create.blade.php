@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Register New Student</h1>
        <a href="{{ route('students.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
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
        <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data">
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
                    <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('phone') border-red-500 @enderror" value="{{ old('phone') }}">
                </div>
                
                <!-- Student Information -->
                <div class="col-span-2 mt-4">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Student Information</h2>
                </div>
                
                <div class="mb-4">
                    <label for="class_id" class="block text-gray-700 text-sm font-bold mb-2">Class *</label>
                    <select name="class_id" id="class_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('class_id') border-red-500 @enderror" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} ({{ $class->department->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="roll_number" class="block text-gray-700 text-sm font-bold mb-2">Roll Number</label>
                    <input type="text" name="roll_number" id="roll_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('roll_number') border-red-500 @enderror" value="{{ old('roll_number') }}">
                </div>
                
                <div class="mb-4">
                    <label for="admission_number" class="block text-gray-700 text-sm font-bold mb-2">Admission Number</label>
                    <input type="text" name="admission_number" id="admission_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('admission_number') border-red-500 @enderror" value="{{ old('admission_number') }}" placeholder="Leave empty for auto-generation (DABM-2025-XXX)">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate admission number</p>
                </div>
                
                <div class="mb-4">
                    <label for="admission_date" class="block text-gray-700 text-sm font-bold mb-2">Admission Date *</label>
                    <input type="date" name="admission_date" id="admission_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('admission_date') border-red-500 @enderror" value="{{ old('admission_date') ?? date('Y-m-d') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="father_name" class="block text-gray-700 text-sm font-bold mb-2">Father's Name *</label>
                    <input type="text" name="father_name" id="father_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('father_name') border-red-500 @enderror" value="{{ old('father_name') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="mother_name" class="block text-gray-700 text-sm font-bold mb-2">Mother's Name *</label>
                    <input type="text" name="mother_name" id="mother_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('mother_name') border-red-500 @enderror" value="{{ old('mother_name') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="guardian_phone" class="block text-gray-700 text-sm font-bold mb-2">Guardian's Phone *</label>
                    <input type="text" name="guardian_phone" id="guardian_phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('guardian_phone') border-red-500 @enderror" value="{{ old('guardian_phone') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="guardian_email" class="block text-gray-700 text-sm font-bold mb-2">Guardian's Email</label>
                    <input type="email" name="guardian_email" id="guardian_email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('guardian_email') border-red-500 @enderror" value="{{ old('guardian_email') }}">
                </div>
                
                <div class="mb-4">
                    <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address *</label>
                    <textarea name="address" id="address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('address') border-red-500 @enderror" required>{{ old('address') }}</textarea>
                </div>
                
                <div class="mb-4">
                    <label for="date_of_birth" class="block text-gray-700 text-sm font-bold mb-2">Date of Birth *</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('date_of_birth') border-red-500 @enderror" value="{{ old('date_of_birth') }}" required>
                </div>
                
                <div class="mb-4">
                    <label for="gender" class="block text-gray-700 text-sm font-bold mb-2">Gender *</label>
                    <select name="gender" id="gender" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('gender') border-red-500 @enderror" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="blood_group" class="block text-gray-700 text-sm font-bold mb-2">Blood Group</label>
                    <select name="blood_group" id="blood_group" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('blood_group') border-red-500 @enderror">
                        <option value="">Select Blood Group</option>
                        <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                        <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                        <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                        <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                        <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                        <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                        <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                        <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                    </select>
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
                    Register Student
                </button>
            </div>
        </form>
    </div>
</div>
@endsection