<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    /**
     * Display a listing of the teachers.
     */
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'department']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('designation', 'like', "%{$search}%");
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $teachers = $query->latest()->paginate(15);
        $departments = Department::all();

        return view('teachers.index', compact('teachers', 'departments'));
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create()
    {
        $departments = Department::all();
        return view('teachers.create', compact('departments'));
    }

    /**
     * Store a newly created teacher in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'avatar' => 'nullable|image|max:2048',
            'department_id' => 'required|exists:departments,id',
            'designation' => 'required|string|max:255',
            'qualification' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'address' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'teacher',
                'phone' => $request->phone,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
                $user->save();
            }

            // Create teacher
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'department_id' => $request->department_id,
                'designation' => $request->designation,
                'qualification' => $request->qualification,
                'phone' => $request->phone,
                'address' => $request->address,
                'joining_date' => $request->joining_date,
                'salary' => $request->salary,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            DB::commit();

            return redirect()->route('teachers.show', $teacher->id)
                ->with('success', 'Teacher registered successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to register teacher. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'department']);

        // Get assigned subjects
        $assignedSubjects = Subject::where('teacher_id', $teacher->id)
            ->with('class')
            ->get();

        // Get assigned classes
        $assignedClasses = ClassRoom::whereHas('subjects', function ($query) use ($teacher) {
            $query->where('teacher_id', $teacher->id);
        })->with('department')->get();

        // Get upcoming exams for teacher's classes
        $upcomingExams = Exam::whereIn('class_id', $assignedClasses->pluck('id'))
            ->where('start_date', '>=', now())
            ->with('class')
            ->orderBy('start_date')
            ->take(5)
            ->get();

        return view('teachers.show', compact(
            'teacher',
            'assignedSubjects',
            'assignedClasses',
            'upcomingExams'
        ));
    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(Teacher $teacher)
    {
        $teacher->load('user');
        $departments = Department::all();
        return view('teachers.edit', compact('teacher', 'departments'));
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($teacher->user_id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'avatar' => 'nullable|image|max:2048',
            'department_id' => 'required|exists:departments,id',
            'designation' => 'required|string|max:255',
            'qualification' => 'required|string|max:255',
            'joining_date' => 'required|date',
            'address' => 'required|string',
            'salary' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            // Update user
            $user = $teacher->user;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->is_active = $request->has('is_active') ? $request->is_active : true;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            $user->save();

            // Update teacher
            $teacher->update([
                'department_id' => $request->department_id,
                'designation' => $request->designation,
                'qualification' => $request->qualification,
                'phone' => $request->phone,
                'address' => $request->address,
                'joining_date' => $request->joining_date,
                'salary' => $request->salary,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            DB::commit();

            return redirect()->route('teachers.show', $teacher->id)
                ->with('success', 'Teacher updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update teacher. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified teacher from storage.
     */
    public function destroy(Teacher $teacher)
    {
        DB::beginTransaction();

        try {
            $user = $teacher->user;

            // Delete avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Remove teacher from assigned subjects
            Subject::where('teacher_id', $teacher->id)->update(['teacher_id' => null]);

            // Delete teacher and associated user
            $teacher->delete();
            $user->delete();

            DB::commit();

            return redirect()->route('teachers.index')
                ->with('success', 'Teacher deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete teacher. ' . $e->getMessage());
        }
    }
}