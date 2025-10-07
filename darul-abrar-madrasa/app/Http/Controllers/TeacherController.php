<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Department;
use App\Models\Teacher;
use App\Repositories\TeacherRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    protected $teacherRepository;

    public function __construct(TeacherRepository $teacherRepository)
    {
        $this->teacherRepository = $teacherRepository;
    }

    /**
     * Display a listing of the teachers.
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->search,
                'department_id' => $request->department_id,
                'status' => $request->status,
            ];

            $teachers = $this->teacherRepository->getAllWithFilters($filters, 15);
            $departments = Department::all();

            return view('teachers.index', compact('teachers', 'departments'));
        } catch (\Exception $e) {
            Log::error('Failed to load teachers list', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load teachers. Please try again.');
        }
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
    public function store(StoreTeacherRequest $request)
    {
        try {
            $teacher = $this->teacherRepository->create($request->validated());

            Log::info('Teacher created successfully', [
                'teacher_id' => $teacher->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('teachers.show', $teacher->id)
                ->with('success', 'Teacher registered successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create teacher', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->except(['password', 'password_confirmation', 'avatar']),
            ]);

            return back()->withInput()->with('error', 'Failed to register teacher. Please try again.');
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(Teacher $teacher)
    {
        try {
            // Load teacher with assignments via repository
            $teacher = $this->teacherRepository->getWithAssignments($teacher->id);

            // Build view-friendly variables expected by the blade
            $assignedSubjects = $teacher->subjects()->with('class')->get();

            $assignedClasses = $assignedSubjects
                ->pluck('class')
                ->filter()
                ->unique('id')
                ->values();

            $upcomingExams = \App\Models\Exam::with('class')
                ->whereIn('class_id', $assignedClasses->pluck('id'))
                ->whereDate('start_date', '>=', now())
                ->orderBy('start_date')
                ->limit(5)
                ->get();

            return view('teachers.show', compact('teacher', 'assignedSubjects', 'assignedClasses', 'upcomingExams'));
        } catch (\Exception $e) {
            Log::error('Failed to load teacher details', [
                'teacher_id' => $teacher->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load teacher details. Please try again.');
        }
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
    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        try {
            $teacher = $this->teacherRepository->update($teacher, $request->validated());

            Log::info('Teacher updated successfully', [
                'teacher_id' => $teacher->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('teachers.show', $teacher->id)
                ->with('success', 'Teacher updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update teacher', [
                'teacher_id' => $teacher->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->except(['password', 'password_confirmation', 'avatar']),
            ]);

            return back()->withInput()->with('error', 'Failed to update teacher. Please try again.');
        }
    }

    /**
     * Remove the specified teacher from storage.
     */
    public function destroy(Teacher $teacher)
    {
        try {
            $teacherId = $teacher->id;
            $this->teacherRepository->delete($teacher);

            Log::info('Teacher deleted successfully', [
                'teacher_id' => $teacherId,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('teachers.index')
                ->with('success', 'Teacher deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete teacher', [
                'teacher_id' => $teacher->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to delete teacher. Please try again.');
        }
    }
}
