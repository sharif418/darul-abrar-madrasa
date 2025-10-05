<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Failed to load subjects list', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load subjects. Please try again.');
        }
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
    public function store(StoreSubjectRequest $request)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $subject = Subject::create($data);

            Log::info('Subject created successfully', [
                'subject_id' => $subject->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('subjects.index')
                ->with('success', 'Subject created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create subject', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to create subject. Please try again.');
        }
    }

    /**
     * Display the specified subject.
     */
    public function show(Subject $subject)
    {
        try {
            $subject->load(['class.department', 'teacher.user', 'results.student.user']);
            
            // Get students enrolled in this subject's class
            $students = $subject->class->students()->with('user')->get();

            return view('subjects.show', compact('subject', 'students'));
        } catch (\Exception $e) {
            Log::error('Failed to load subject details', [
                'subject_id' => $subject->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load subject details. Please try again.');
        }
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
    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $subject->update($data);

            Log::info('Subject updated successfully', [
                'subject_id' => $subject->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('subjects.index')
                ->with('success', 'Subject updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update subject', [
                'subject_id' => $subject->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to update subject. Please try again.');
        }
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject)
    {
        try {
            // Check if subject has results
            if ($subject->results()->count() > 0) {
                Log::warning('Attempted to delete subject with existing results', [
                    'subject_id' => $subject->id,
                    'results_count' => $subject->results()->count(),
                    'user_id' => auth()->id(),
                ]);

                return redirect()->route('subjects.index')
                    ->with('error', 'Cannot delete subject with existing results. Please remove results first.');
            }

            $subjectId = $subject->id;
            $subject->delete();

            Log::info('Subject deleted successfully', [
                'subject_id' => $subjectId,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('subjects.index')
                ->with('success', 'Subject deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete subject', [
                'subject_id' => $subject->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to delete subject. Please try again.');
        }
    }
}
