<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Department;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index(Request $request)
    {
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'class_numeric' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        ClassRoom::create($validated);

        return redirect()->route('classes.index')
            ->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified class.
     */
    public function show(ClassRoom $class)
    {
        $class->load(['department', 'students.user', 'subjects.teacher.user']);
        $class->loadCount(['students', 'subjects', 'exams']);

        // Get recent exams
        $recentExams = $class->exams()
            ->latest()
            ->take(5)
            ->get();

        return view('classes.show', compact('class', 'recentExams'));
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
    public function update(Request $request, ClassRoom $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'class_numeric' => 'nullable|string|max:50',
            'section' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $class->update($validated);

        return redirect()->route('classes.index')
            ->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified class from storage.
     */
    public function destroy(ClassRoom $class)
    {
        // Check if class has students
        if ($class->students()->count() > 0) {
            return redirect()->route('classes.index')
                ->with('error', 'Cannot delete class with enrolled students. Please transfer or remove students first.');
        }

        // Check if class has subjects
        if ($class->subjects()->count() > 0) {
            return redirect()->route('classes.index')
                ->with('error', 'Cannot delete class with assigned subjects. Please remove subjects first.');
        }

        $class->delete();

        return redirect()->route('classes.index')
            ->with('success', 'Class deleted successfully.');
    }
}
