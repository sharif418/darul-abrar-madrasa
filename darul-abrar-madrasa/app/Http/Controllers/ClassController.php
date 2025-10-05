<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use App\Models\ClassRoom;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index(Request $request)
    {
        try {
            $query = ClassRoom::with('department')->withCount(['students', 'subjects']);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('class_numeric', 'like', "%{$search}%")
                      ->orWhere('section', 'like', "%{$search}%");
                });
            }

            // Filter by department
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Filter by status
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $classes = $query->latest()->paginate(15);
            $departments = Department::where('is_active', true)->get();

            return view('classes.index', compact('classes', 'departments'));
        } catch (\Exception $e) {
            Log::error('Failed to load classes list', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load classes. Please try again.');
        }
    }

    /**
     * Show the form for creating a new class.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        return view('classes.create', compact('departments'));
    }

    /**
     * Store a newly created class in storage.
     */
    public function store(StoreClassRequest $request)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $class = ClassRoom::create($data);

            Log::info('Class created successfully', [
                'class_id' => $class->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('classes.index')
                ->with('success', 'Class created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create class', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to create class. Please try again.');
        }
    }

    /**
     * Display the specified class.
     */
    public function show(ClassRoom $class)
    {
        try {
            $class->load(['department', 'students.user', 'subjects.teacher.user']);
            $class->loadCount(['students', 'subjects', 'exams']);

            // Get recent exams
            $recentExams = $class->exams()
                ->latest()
                ->take(5)
                ->get();

            return view('classes.show', compact('class', 'recentExams'));
        } catch (\Exception $e) {
            Log::error('Failed to load class details', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load class details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(ClassRoom $class)
    {
        $departments = Department::where('is_active', true)->get();
        return view('classes.edit', compact('class', 'departments'));
    }

    /**
     * Update the specified class in storage.
     */
    public function update(UpdateClassRequest $request, ClassRoom $class)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $class->update($data);

            Log::info('Class updated successfully', [
                'class_id' => $class->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('classes.index')
                ->with('success', 'Class updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update class', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to update class. Please try again.');
        }
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(ClassRoom $class)
    {
        try {
            // Check if class has students
            if ($class->students()->count() > 0) {
                Log::warning('Attempted to delete class with enrolled students', [
                    'class_id' => $class->id,
                    'students_count' => $class->students()->count(),
                    'user_id' => auth()->id(),
                ]);

                return redirect()->route('classes.index')
                    ->with('error', 'Cannot delete class with enrolled students. Please transfer or remove students first.');
            }

            // Check if class has subjects
            if ($class->subjects()->count() > 0) {
                Log::warning('Attempted to delete class with assigned subjects', [
                    'class_id' => $class->id,
                    'subjects_count' => $class->subjects()->count(),
                    'user_id' => auth()->id(),
                ]);

                return redirect()->route('classes.index')
                    ->with('error', 'Cannot delete class with assigned subjects. Please remove subjects first.');
            }

            $classId = $class->id;
            $class->delete();

            Log::info('Class deleted successfully', [
                'class_id' => $classId,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('classes.index')
                ->with('success', 'Class deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete class', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to delete class. Please try again.');
        }
    }
}
