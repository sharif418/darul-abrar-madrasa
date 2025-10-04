<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index(Request $request)
    {
        $query = Subject::with(['class.department', 'teacher.user']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $subjects = $query->latest()->paginate(15);
        $classes = ClassRoom::where('is_active', true)->with('department')->get();
        $teachers = Teacher::where('is_active', true)->with('user')->get();

        return view('subjects.index', compact('subjects', 'classes', 'teachers'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        $classes = ClassRoom::where('is_active', true)->with('department')->get();
        $teachers = Teacher::where('is_active', true)->with('user')->get();
        return view('subjects.create', compact('classes', 'teachers'));
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects',
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'full_mark' => 'required|integer|min:1',
            'pass_mark' => 'required|integer|min:1|lt:full_mark',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        Subject::create($validated);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        $subject->load(['class.department', 'teacher.user', 'results.student.user']);
        
        // Get students enrolled in this subject's class
        $students = $subject->class->students()->with('user')->get();

        return view('subjects.show', compact('subject', 'students'));
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject)
    {
        $classes = ClassRoom::where('is_active', true)->with('department')->get();
        $teachers = Teacher::where('is_active', true)->with('user')->get();
        return view('subjects.edit', compact('subject', 'classes', 'teachers'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('subjects')->ignore($subject->id)],
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'full_mark' => 'required|integer|min:1',
            'pass_mark' => 'required|integer|min:1|lt:full_mark',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $subject->update($validated);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject)
    {
        // Check if subject has results
        if ($subject->results()->count() > 0) {
            return redirect()->route('subjects.index')
                ->with('error', 'Cannot delete subject with existing results. Please remove results first.');
        }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
