<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Exam;
use App\Models\GradingScale;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreMarksRequest;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ResultController extends Controller
{
    /**
     * Display a listing of the results.
     */
    public function index(Request $request)
    {
        $query = Result::with(['student.user', 'exam', 'subject']);

        // Apply filters
        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $results = $query->latest()->paginate(15);
        
        // Get data for filters
        $exams = Exam::where('is_result_published', true)->orWhere(function ($query) {
            $query->where('end_date', '<', now())
                  ->where('is_active', true);
        })->get();
        
        $classes = ClassRoom::with('department')->get();
        $subjects = Subject::all();
        $students = Student::with('user')->get();

        // Calculate statistics if specific exam and subject are selected
        $totalStudents = 0;
        $passedStudents = 0;
        $failedStudents = 0;
        $passRate = 0;
        $averageMark = 0;
        $exam = null;
        $subject = null;

        if ($request->filled('exam_id') && $request->filled('subject_id')) {
            $exam = Exam::find($request->exam_id);
            $subject = Subject::find($request->subject_id);
            
            if ($exam && $subject) {
                $resultsStats = Result::where('exam_id', $request->exam_id)
                    ->where('subject_id', $request->subject_id)
                    ->get();
                    
                $totalStudents = $resultsStats->count();
                $passedStudents = $resultsStats->where('is_passed', true)->count();
                $failedStudents = $totalStudents - $passedStudents;
                $passRate = $totalStudents > 0 ? round(($passedStudents / $totalStudents) * 100) : 0;
                $averageMark = $totalStudents > 0 ? round($resultsStats->avg('marks_obtained'), 2) : 0;
            }
        }

        return view('results.index', compact(
            'results', 
            'exams', 
            'classes', 
            'subjects', 
            'students',
            'totalStudents',
            'passedStudents',
            'failedStudents',
            'passRate',
            'averageMark',
            'exam',
            'subject'
        ));
    }

    /**
     * Show the form for creating bulk results.
     */
    public function createBulk($exam_id, $class_id, $subject_id)
    {
        $exam = Exam::findOrFail($exam_id);
        $class = ClassRoom::findOrFail($class_id);
        $subject = Subject::findOrFail($subject_id);
        
        // Check if the exam is completed
        if (now() < $exam->end_date) {
            return redirect()->route('exams.show', $exam->id)
                ->with('error', 'Cannot enter results before the exam is completed.');
        }
        
        // Check if results are already published
        if ($exam->is_result_published) {
            return redirect()->route('results.index', ['exam_id' => $exam->id, 'subject_id' => $subject->id])
                ->with('error', 'Results for this exam have already been published and cannot be modified.');
        }
        
        // Get all students in the class
        $students = Student::where('class_id', $class_id)
            ->where('is_active', true)
            ->with('user')
            ->get();
            
        // Get all subjects for the class
        $subjects = Subject::where('class_id', $class_id)->get();
        
        // Get grading scales for reference
        $gradingScales = GradingScale::active()->orderBy('min_mark', 'desc')->get();
        
        // Check if results already exist for some students
        $existingResults = Result::where('exam_id', $exam_id)
            ->where('subject_id', $subject_id)
            ->get()
            ->keyBy('student_id')
            ->toArray();

        return view('results.create-bulk', compact('exam', 'class', 'subject', 'students', 'subjects', 'existingResults', 'gradingScales'));
    }

    /**
     * Store multiple results in storage.
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'marks' => 'required|array',
            'marks.*' => 'required|numeric|min:0',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $subject = Subject::findOrFail($request->subject_id);
        
        // Check if the exam is completed
        if (now() < $exam->end_date) {
            return back()->with('error', 'Cannot enter results before the exam is completed.');
        }
        
        // Check if results are already published
        if ($exam->is_result_published) {
            return back()->with('error', 'Results for this exam have already been published and cannot be modified.');
        }

        DB::beginTransaction();

        try {
            $student_ids = $request->student_ids;
            $marks = $request->marks;
            $remarks = $request->remarks ?? [];

            foreach ($student_ids as $student_id) {
                if (!isset($marks[$student_id])) {
                    continue;
                }

                $mark = $marks[$student_id];
                $remark = $remarks[$student_id] ?? null;

                // Check if result already exists
                $result = Result::where('exam_id', $request->exam_id)
                    ->where('subject_id', $request->subject_id)
                    ->where('student_id', $student_id)
                    ->first();

                if ($result) {
                    // Update existing result
                    $result->marks_obtained = $mark;
                    $result->remarks = $remark;
                    $result->created_by = Auth::id();
                    
                    // Calculate grade and GPA
                    $result->calculateGradeAndGpa();
                    $result->save();
                } else {
                    // Create new result
                    $result = new Result([
                        'exam_id' => $request->exam_id,
                        'subject_id' => $request->subject_id,
                        'student_id' => $student_id,
                        'marks_obtained' => $mark,
                        'remarks' => $remark,
                        'created_by' => Auth::id(),
                    ]);
                    
                    // Calculate grade and GPA
                    $result->calculateGradeAndGpa();
                    $result->save();
                }
            }

            DB::commit();

            return redirect()->route('exams.show', $request->exam_id)
                ->with('success', 'Results saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save results. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified result.
     */
    public function show(Result $result)
    {
        $result->load(['student.user', 'exam', 'subject', 'createdBy']);
        return view('results.show', compact('result'));
    }

    /**
     * Show the form for editing the specified result.
     */
    public function edit(Result $result)
    {
        // Check if results are already published
        if ($result->exam->is_result_published) {
            return redirect()->route('results.index')
                ->with('error', 'Results for this exam have already been published and cannot be modified.');
        }
        
        $result->load(['student.user', 'exam', 'subject', 'createdBy']);
        $gradingScales = GradingScale::active()->orderBy('min_mark', 'desc')->get();
        
        return view('results.edit', compact('result', 'gradingScales'));
    }

    /**
     * Update the specified result in storage.
     */
    public function update(Request $request, Result $result)
    {
        // Check if results are already published
        if ($result->exam->is_result_published) {
            return redirect()->route('results.index')
                ->with('error', 'Results for this exam have already been published and cannot be modified.');
        }
        
        $request->validate([
            'marks_obtained' => 'required|numeric|min:0|max:' . $result->subject->full_mark,
            'remarks' => 'nullable|string|max:255',
        ]);

        $result->marks_obtained = $request->marks_obtained;
        $result->remarks = $request->remarks;
        $result->created_by = Auth::id();
        
        // Calculate grade and GPA
        $result->calculateGradeAndGpa();
        $result->save();

        return redirect()->route('results.index', ['exam_id' => $result->exam_id, 'subject_id' => $result->subject_id])
            ->with('success', 'Result updated successfully.');
    }

    /**
     * Remove the specified result from storage.
     */
    public function destroy(Result $result)
    {
        // Check if results are already published
        if ($result->exam->is_result_published) {
            return back()->with('error', 'Results for this exam have already been published and cannot be deleted.');
        }
        
        $result->delete();

        return back()->with('success', 'Result deleted successfully.');
    }

    /**
     * Display the student's own results.
     */
    public function myResults(Request $request)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            abort(403, 'You are not registered as a student.');
        }

        // Get all exams with published results for this student's class
        $exams = Exam::where('class_id', $student->class_id)
            ->where('is_result_published', true)
            ->orderBy('start_date', 'desc')
            ->get();
            
        // Get exam summaries
        $examSummaries = [];
        $selectedExam = null;
        $examResults = collect();
        $overallResult = null;
        $position = 'N/A';
        
        foreach ($exams as $exam) {
            $results = Result::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->with('subject')
                ->get();
                
            if ($results->count() > 0) {
                $totalFullMarks = $results->sum(function ($result) {
                    return $result->subject->full_mark;
                });
                
                $totalObtainedMarks = $results->sum('marks_obtained');
                $totalGpaPoints = $results->sum('gpa_point');
                $averageGpa = $results->count() > 0 ? $totalGpaPoints / $results->count() : 0;
                
                $failedSubjects = $results->where('is_passed', false)->count();
                
                $examSummaries[$exam->id] = [
                    'name' => $exam->name,
                    'date' => $exam->start_date->format('M Y'),
                    'subjects' => $results->count(),
                    'full' => $totalFullMarks,
                    'obtained' => $totalObtainedMarks,
                    'percentage' => $totalFullMarks > 0 ? round(($totalObtainedMarks / $totalFullMarks) * 100, 2) : 0,
                    'average_gpa' => round($averageGpa, 2),
                    'failed' => $failedSubjects,
                ];
            }
        }
        
        // If exam_id is provided, get detailed results for that exam
        if ($request->filled('exam_id') && isset($examSummaries[$request->exam_id])) {
            $selectedExam = Exam::find($request->exam_id);
            
            $examResults = Result::where('exam_id', $request->exam_id)
                ->where('student_id', $student->id)
                ->with(['subject', 'exam'])
                ->get();
                
            // Get overall result
            $overallResult = $selectedExam->getStudentResult($student->id);
                
            // Calculate position in class
            $classResults = $selectedExam->getClassRankList();
            
            foreach ($classResults as $studentId => $result) {
                if ($studentId == $student->id) {
                    $position = $result['rank'];
                    break;
                }
            }
            
            // Format position with suffix
            if (is_numeric($position)) {
                $suffix = 'th';
                if ($position % 10 == 1 && $position % 100 != 11) {
                    $suffix = 'st';
                } elseif ($position % 10 == 2 && $position % 100 != 12) {
                    $suffix = 'nd';
                } elseif ($position % 10 == 3 && $position % 100 != 13) {
                    $suffix = 'rd';
                }
                
                $position = $position . $suffix;
            }
        }
        
        return view('results.my-results', compact(
            'student',
            'exams',
            'examSummaries',
            'selectedExam',
            'examResults',
            'overallResult',
            'position'
        ));
    }
    
    /**
     * Generate PDF mark sheet for a student.
     */
    public function generateMarkSheet($examId, $studentId)
    {
        $exam = Exam::findOrFail($examId);
        $student = Student::with('user', 'class')->findOrFail($studentId);
        
        // Check if the exam is published
        if (!$exam->is_result_published) {
            return back()->with('error', 'Results for this exam have not been published yet.');
        }
        
        // Check if the student belongs to the exam's class
        if ($student->class_id != $exam->class_id) {
            return back()->with('error', 'This student does not belong to the class for this exam.');
        }
        
        // Get the student's results
        $results = Result::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->with('subject')
            ->get();
            
        if ($results->isEmpty()) {
            return back()->with('error', 'No results found for this student in this exam.');
        }
        
        // Get overall result
        $overallResult = $exam->getStudentResult($studentId);
        
        // Calculate position in class
        $classResults = $exam->getClassRankList();
        $position = 'N/A';
        
        foreach ($classResults as $sId => $result) {
            if ($sId == $studentId) {
                $position = $result['rank'];
                break;
            }
        }
        
        // Format position with suffix
        if (is_numeric($position)) {
            $suffix = 'th';
            if ($position % 10 == 1 && $position % 100 != 11) {
                $suffix = 'st';
            } elseif ($position % 10 == 2 && $position % 100 != 12) {
                $suffix = 'nd';
            } elseif ($position % 10 == 3 && $position % 100 != 13) {
                $suffix = 'rd';
            }
            
            $position = $position . $suffix;
        }
        
        // Get grading scales for reference
        $gradingScales = GradingScale::active()->orderBy('min_mark', 'desc')->get();
        
        // Generate PDF
        $pdf = PDF::loadView('results.mark-sheet', compact(
            'exam',
            'student',
            'results',
            'overallResult',
            'position',
            'gradingScales'
        ));
        
        return $pdf->download('mark_sheet_' . $student->student_id . '_' . $exam->name . '.pdf');
    }
    
    /**
     * Publish exam results.
     */
    public function publishResults(Exam $exam)
    {
        // Check if the exam is completed
        if (now() < $exam->end_date) {
            return back()->with('error', 'Cannot publish results before the exam is completed.');
        }
        
        // Check if all students have results for all subjects
        $students = Student::where('class_id', $exam->class_id)->where('is_active', true)->get();
        $subjects = Subject::where('class_id', $exam->class_id)->get();
        
        $missingResults = [];
        
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                $result = Result::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->first();
                    
                if (!$result) {
                    $missingResults[] = [
                        'student' => $student->name,
                        'subject' => $subject->name
                    ];
                }
            }
        }
        
        if (!empty($missingResults)) {
            return back()->with('error', 'Cannot publish results. Some students are missing results for certain subjects.')
                ->with('missingResults', $missingResults);
        }
        
        // Publish results
        $exam->update([
            'is_result_published' => true
        ]);
        
        return back()->with('success', 'Results published successfully.');
    }
    
    /**
     * View class result summary.
     */
    public function classResultSummary(Exam $exam)
    {
        // Check if the exam is published
        if (!$exam->is_result_published) {
            return back()->with('error', 'Results for this exam have not been published yet.');
        }
        
        // Get class rank list
        $rankList = $exam->getClassRankList();
        
        // Get subjects for this class
        $subjects = Subject::where('class_id', $exam->class_id)->get();
        
        // Get grading scales for reference
        $gradingScales = GradingScale::active()->orderBy('min_mark', 'desc')->get();
        
        return view('results.class-summary', compact('exam', 'rankList', 'subjects', 'gradingScales'));
    }
    
    /**
     * Show the form for entering marks.
     */
    public function createMarks()
    {
        return view('marks.create');
    }

    /**
     * Store marks for multiple students.
     */
    public function storeMarks(StoreMarksRequest $request)
    {
        $validated = $request->validated();
        $examId = $validated['exam_id'];
        $marks = $validated['marks'];
        $gradingScales = GradingScale::orderBy('min_mark', 'desc')->get();
        
        // Get all subjects for the class to validate subject IDs
        $subjects = Subject::where('class_id', $validated['class_id'])->get();
        
        // Process marks for each student
        foreach ($marks as $studentId => $subjectMarks) {
            foreach ($subjectMarks as $subjectId => $marksObtained) {
                if ($marksObtained === '' || $marksObtained === null) {
                    continue; // Skip empty marks
                }
                
                // Validate student and subject
                $student = Student::find($studentId);
                $subject = $subjects->firstWhere('id', $subjectId);
                
                if (!$student || !$subject) {
                    continue; // Skip invalid student or subject
                }
                
                // Calculate percentage
                $percentage = ($marksObtained / $subject->full_mark) * 100;
                
                // Determine grade and GPA
                $grade = 'F';
                $gpaPoint = 0;
                $isPassed = $marksObtained >= $subject->pass_mark;
                
                if ($isPassed) {
                    foreach ($gradingScales as $scale) {
                        if ($percentage >= $scale->min_mark) {
                            $grade = $scale->grade;
                            $gpaPoint = $scale->gpa;
                            break;
                        }
                    }
                }
                
                // Update or create result
                Result::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'exam_id' => $examId,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'marks_obtained' => $marksObtained,
                        'grade' => $grade,
                        'gpa_point' => $gpaPoint,
                        'is_passed' => $isPassed,
                        'remarks' => '',
                    ]
                );
            }
        }
        
        return redirect()->back()->with('success', 'Marks saved successfully!');
    }

    /**
     * Generate PDF class result summary.
     */
    public function generateClassResultSummary(Exam $exam)
    {
        // Check if the exam is published
        if (!$exam->is_result_published) {
            return back()->with('error', 'Results for this exam have not been published yet.');
        }
        
        // Get class rank list
        $rankList = $exam->getClassRankList();
        
        // Get subjects for this class
        $subjects = Subject::where('class_id', $exam->class_id)->get();
        
        // Get grading scales for reference
        $gradingScales = GradingScale::active()->orderBy('min_mark', 'desc')->get();
        
        // Generate PDF
        $pdf = PDF::loadView('results.class-summary-pdf', compact(
            'exam',
            'rankList',
            'subjects',
            'gradingScales'
        ));
        
        return $pdf->download('class_result_summary_' . $exam->name . '.pdf');
    }
}