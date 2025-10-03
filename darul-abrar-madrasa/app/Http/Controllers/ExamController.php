<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Display a listing of the exams.
     */
    public function index(Request $request)
    {
        $query = Exam::with(['class.department']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            $now = now();
            
            if ($request->status === 'upcoming') {
                $query->where('start_date', '>', $now);
            } elseif ($request->status === 'ongoing') {
                $query->where('start_date', '<=', $now)
                      ->where('end_date', '>=', $now);
            } elseif ($request->status === 'completed') {
                $query->where('end_date', '<', $now);
            }
        }

        if ($request->filled('result_status')) {
            $query->where('is_result_published', $request->result_status === 'published');
        }

        $exams = $query->latest()->paginate(15);
        $classes = ClassRoom::with('department')->get();

        return view('exams.index', compact('exams', 'classes'));
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create()
    {
        $classes = ClassRoom::with('department')->get();
        return view('exams.create', compact('classes'));
    }

    /**
     * Store a newly created exam in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_result_published' => 'boolean',
        ]);

        $exam = Exam::create([
            'name' => $request->name,
            'class_id' => $request->class_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
            'is_result_published' => $request->has('is_result_published') ? $request->is_result_published : false,
        ]);

        return redirect()->route('exams.show', $exam->id)
            ->with('success', 'Exam created successfully.');
    }

    /**
     * Display the specified exam.
     */
    public function show(Exam $exam)
    {
        $exam->load('class.department');
        
        // Get subjects for this class
        $subjects = Subject::where('class_id', $exam->class_id)
            ->with('teacher.user')
            ->get();
            
        // If results are published, get result statistics
        $totalStudents = 0;
        $passedStudents = 0;
        $failedStudents = 0;
        $passRate = 0;
        
        if ($exam->is_result_published) {
            $totalStudents = Student::where('class_id', $exam->class_id)->count();
            
            // Get unique students who have results for this exam
            $studentResults = DB::table('results')
                ->select('student_id', DB::raw('COUNT(*) as subject_count'), DB::raw('SUM(CASE WHEN marks_obtained >= (SELECT pass_mark FROM subjects WHERE subjects.id = results.subject_id) THEN 1 ELSE 0 END) as passed_subjects'))
                ->where('exam_id', $exam->id)
                ->groupBy('student_id')
                ->get();
                
            // Count students who passed all subjects
            foreach ($studentResults as $studentResult) {
                if ($studentResult->subject_count === $studentResult->passed_subjects) {
                    $passedStudents++;
                } else {
                    $failedStudents++;
                }
            }
            
            // Calculate pass rate
            $passRate = $totalStudents > 0 ? round(($passedStudents / $totalStudents) * 100) : 0;
        }

        return view('exams.show', compact(
            'exam', 
            'subjects', 
            'totalStudents', 
            'passedStudents', 
            'failedStudents', 
            'passRate'
        ));
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam)
    {
        $classes = ClassRoom::with('department')->get();
        return view('exams.edit', compact('exam', 'classes'));
    }

    /**
     * Update the specified exam in storage.
     */
    public function update(Request $request, Exam $exam)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_result_published' => 'boolean',
        ]);

        // If results are already published, don't allow unpublishing
        if ($exam->is_result_published) {
            $request->merge(['is_result_published' => true]);
        }

        $exam->update([
            'name' => $request->name,
            'class_id' => $request->class_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
            'is_result_published' => $request->has('is_result_published') ? $request->is_result_published : false,
        ]);

        return redirect()->route('exams.show', $exam->id)
            ->with('success', 'Exam updated successfully.');
    }

    /**
     * Remove the specified exam from storage.
     */
    public function destroy(Exam $exam)
    {
        // Check if there are any results for this exam
        $resultsCount = Result::where('exam_id', $exam->id)->count();
        
        if ($resultsCount > 0) {
            return back()->with('error', 'Cannot delete exam because it has associated results. Delete the results first.');
        }
        
        $exam->delete();

        return redirect()->route('exams.index')
            ->with('success', 'Exam deleted successfully.');
    }

    /**
     * Publish the results for the specified exam.
     */
    public function publishResults(Exam $exam)
    {
        // Check if the exam is completed
        if (now() <= $exam->end_date) {
            return back()->with('error', 'Cannot publish results before the exam is completed.');
        }
        
        // Check if all subjects have results
        $subjects = Subject::where('class_id', $exam->class_id)->get();
        $students = Student::where('class_id', $exam->class_id)->get();
        
        foreach ($subjects as $subject) {
            foreach ($students as $student) {
                $result = Result::where('exam_id', $exam->id)
                    ->where('subject_id', $subject->id)
                    ->where('student_id', $student->id)
                    ->first();
                    
                if (!$result) {
                    return back()->with('error', "Results for all students and subjects must be entered before publishing. Missing result for {$student->user->name} in {$subject->name}.");
                }
            }
        }
        
        $exam->update(['is_result_published' => true]);
        
        return redirect()->route('exams.show', $exam->id)
            ->with('success', 'Exam results published successfully.');
    }
}