<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index(Request $request)
    {
        $query = Student::with(['user', 'class.department']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('admission_number', 'like', "%{$search}%");
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $students = $query->latest()->paginate(15);
        $classes = ClassRoom::with('department')->get();

        return view('students.index', compact('students', 'classes'));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $classes = ClassRoom::with('department')->get();
        return view('students.create', compact('classes'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|image|max:2048',
            'class_id' => 'required|exists:classes,id',
            'roll_number' => 'nullable|string|max:255',
            'admission_number' => 'nullable|string|max:255|unique:students',
            'admission_date' => 'required|date',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'guardian_phone' => 'required|string|max:15',
            'guardian_email' => 'nullable|string|email|max:255',
            'address' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'phone' => $request->phone,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
                $user->save();
            }

            // Generate unique student ID if not provided
            $admissionNumber = $request->admission_number;
            if (empty($admissionNumber)) {
                $admissionNumber = $this->generateStudentId();
            }

            // Create student
            $student = Student::create([
                'user_id' => $user->id,
                'class_id' => $request->class_id,
                'roll_number' => $request->roll_number,
                'admission_number' => $admissionNumber,
                'admission_date' => $request->admission_date,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'guardian_phone' => $request->guardian_phone,
                'guardian_email' => $request->guardian_email,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            DB::commit();

            return redirect()->route('students.show', $student->id)
                ->with('success', 'Student registered successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to register student. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        $student->load(['user', 'class.department']);

        // Get attendance statistics
        $presentCount = $student->attendances()->where('status', 'present')->count();
        $absentCount = $student->attendances()->where('status', 'absent')->count();
        $lateCount = $student->attendances()->where('status', 'late')->count();
        $totalAttendance = $presentCount + $absentCount + $lateCount;
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;

        // Get recent results
        $recentResults = $student->results()->with(['exam', 'subject'])->latest()->take(5)->get();

        // Get pending fees
        $pendingFees = $student->fees()->whereIn('status', ['unpaid', 'partial'])->get();

        return view('students.show', compact(
            'student',
            'presentCount',
            'absentCount',
            'lateCount',
            'attendanceRate',
            'recentResults',
            'pendingFees'
        ));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        $student->load('user');
        $classes = ClassRoom::with('department')->get();
        return view('students.edit', compact('student', 'classes'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($student->user_id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|image|max:2048',
            'class_id' => 'required|exists:classes,id',
            'roll_number' => 'nullable|string|max:255',
            'admission_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('students')->ignore($student->id),
            ],
            'admission_date' => 'required|date',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'guardian_phone' => 'required|string|max:15',
            'guardian_email' => 'nullable|string|email|max:255',
            'address' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            // Update user
            $user = $student->user;
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

            // Update student
            $student->update([
                'class_id' => $request->class_id,
                'roll_number' => $request->roll_number,
                'admission_number' => $request->admission_number,
                'admission_date' => $request->admission_date,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'guardian_phone' => $request->guardian_phone,
                'guardian_email' => $request->guardian_email,
                'address' => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            DB::commit();

            return redirect()->route('students.show', $student->id)
                ->with('success', 'Student updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update student. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        DB::beginTransaction();

        try {
            $user = $student->user;

            // Delete avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Delete student and associated user
            $student->delete();
            $user->delete();

            DB::commit();

            return redirect()->route('students.index')
                ->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete student. ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique student ID.
     */
    private function generateStudentId()
    {
        $prefix = 'DABM-' . date('Y') . '-';
        $lastStudent = Student::where('admission_number', 'like', $prefix . '%')
            ->orderBy('admission_number', 'desc')
            ->first();

        if ($lastStudent) {
            // Extract the numeric part and increment
            $lastId = substr($lastStudent->admission_number, strlen($prefix));
            $nextId = str_pad((int)$lastId + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // First student with this prefix
            $nextId = '001';
        }

        return $prefix . $nextId;
    }
}