<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            // Filter by status
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $users = $query->latest()->paginate(15);

            return view('users.index', compact('users'));
        } catch (\Exception $e) {
            Log::error('Failed to load users list', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load users. Please try again.');
        }
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = ['admin', 'teacher', 'student', 'staff'];
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $user = User::create($data);

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'created_by' => Auth::id(),
                'data' => $request->except('password'),
            ]);

            return back()->withInput()->with('error', 'Failed to create user. Please try again.');
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        try {
            // Load relationships based on user role
            if ($user->isStudent() && $user->student) {
                $user->load(['student.class', 'student.fees', 'student.attendances']);
            } elseif ($user->isTeacher() && $user->teacher) {
                $user->load(['teacher.department', 'teacher.subjects']);
            }

            return view('users.show', compact('user'));
        } catch (\Exception $e) {
            Log::error('Failed to load user details', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'viewed_by' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load user details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Prevent editing own role or status
        $canEditRole = Auth::id() !== $user->id;
        $roles = ['admin', 'teacher', 'student', 'staff'];
        
        return view('users.edit', compact('user', 'roles', 'canEditRole'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            // Prevent changing own role or deactivating own account
            if (Auth::id() === $user->id) {
                if ($request->filled('role') && $request->role !== $user->role) {
                    return back()->withInput()->with('error', 'You cannot change your own role.');
                }
                
                if ($request->filled('is_active') && !$request->boolean('is_active')) {
                    return back()->withInput()->with('error', 'You cannot deactivate your own account.');
                }
            }

            $data = $request->validated();

            if ($request->filled('password')) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $user->update($data);

            Log::info('User updated successfully', [
                'user_id' => $user->id,
                'updated_by' => Auth::id(),
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'updated_by' => Auth::id(),
                'data' => $request->except('password'),
            ]);

            return back()->withInput()->with('error', 'Failed to update user. Please try again.');
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deleting own account
            if ($user->id === Auth::id()) {
                Log::warning('Attempted to delete own account', [
                    'user_id' => $user->id,
                ]);

                return redirect()->route('users.index')
                    ->with('error', 'You cannot delete your own account.');
            }

            // Check if user has related records
            if ($user->isStudent() && $user->student) {
                return redirect()->route('users.index')
                    ->with('error', 'Cannot delete user with student profile. Please delete the student profile first.');
            }

            if ($user->isTeacher() && $user->teacher) {
                return redirect()->route('users.index')
                    ->with('error', 'Cannot delete user with teacher profile. Please delete the teacher profile first.');
            }

            $userId = $user->id;
            $user->delete();

            Log::info('User deleted successfully', [
                'user_id' => $userId,
                'deleted_by' => Auth::id(),
            ]);

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'deleted_by' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to delete user. Please try again.');
        }
    }
}
