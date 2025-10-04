@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('users.index') }}" class="hover:text-blue-600">Users</a>
            <span>/</span>
            <span class="text-gray-900">User Details</span>
        </div>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">User Details</h1>
            <div class="flex gap-2">
                <a href="{{ route('users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Edit User
                </a>
                <a href="{{ route('users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="text-center">
                    @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" 
                        class="w-32 h-32 rounded-full mx-auto mb-4 object-cover">
                    @else
                    <div class="w-32 h-32 rounded-full bg-blue-500 flex items-center justify-center text-white text-4xl font-bold mx-auto mb-4">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @endif
                    
                    <h2 class="text-xl font-bold text-gray-900 mb-1">{{ $user->name }}</h2>
                    <p class="text-gray-600 mb-3">{{ $user->email }}</p>
                    
                    <div class="flex justify-center gap-2 mb-4">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full 
                            @if($user->role == 'admin') bg-purple-100 text-purple-800
                            @elseif($user->role == 'teacher') bg-blue-100 text-blue-800
                            @elseif($user->role == 'student') bg-green-100 text-green-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                        
                        @if($user->is_active)
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                        @else
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            Inactive
                        </span>
                        @endif
                    </div>
                </div>

                <div class="border-t pt-4 mt-4">
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">Phone</label>
                            <p class="text-gray-900">{{ $user->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Member Since</label>
                            <p class="text-gray-900">{{ $user->created_at->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Last Updated</label>
                            <p class="text-gray-900">{{ $user->updated_at->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">User ID</label>
                        <p class="text-gray-900">#{{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                        <p class="text-gray-900">{{ $user->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Phone Number</label>
                        <p class="text-gray-900">{{ $user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Role</label>
                        <p class="text-gray-900">{{ ucfirst($user->role) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Account Status</label>
                        <p class="text-gray-900">
                            @if($user->is_active)
                            <span class="text-green-600">● Active</span>
                            @else
                            <span class="text-red-600">● Inactive</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Email Verified</label>
                        <p class="text-gray-900">
                            @if($user->email_verified_at)
                            <span class="text-green-600">● Verified</span>
                            @else
                            <span class="text-yellow-600">● Not Verified</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Activity Statistics (if applicable) -->
            @if($user->role == 'teacher')
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Teaching Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">0</div>
                        <div class="text-sm text-gray-600">Classes Assigned</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">0</div>
                        <div class="text-sm text-gray-600">Subjects Teaching</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">0</div>
                        <div class="text-sm text-gray-600">Students</div>
                    </div>
                </div>
            </div>
            @endif

            @if($user->role == 'student')
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Academic Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">0%</div>
                        <div class="text-sm text-gray-600">Attendance</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">0</div>
                        <div class="text-sm text-gray-600">Exams Taken</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">N/A</div>
                        <div class="text-sm text-gray-600">Average Grade</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                <div class="space-y-3">
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5"></div>
                        <div>
                            <p class="text-gray-900">Account created</p>
                            <p class="text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @if($user->updated_at != $user->created_at)
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5"></div>
                        <div>
                            <p class="text-gray-900">Profile updated</p>
                            <p class="text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
