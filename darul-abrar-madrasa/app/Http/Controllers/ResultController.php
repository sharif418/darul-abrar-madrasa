<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMarksRequest;
use App\Http\Requests\StoreResultRequest;
use App\Models\ClassRoom;
use App\Models\Exam;
use App\Models\GradingScale;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Repositories\ResultRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ResultController extends Controller
{
    protected $resultRepository;

    public function __construct(ResultRepository $resultRepository)
    {
        $this->resultRepository = $resultRepository;
    }

    /**
     * Display a listing of the results.
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'exam_id' => $request->exam_id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'student_id' => $request->student_id,
            ];

            $results = $this->resultRepository->getAllWithFilters($filters, 15);
            
            // Get data for filters
            $exams = Exam::where('is_result_published', true)
                ->orWhere(function ($query) {
                    $query->where('end_date', '<', now())->where('is_active', true);
                })->get();
            
            $classes = ClassRoom::with('department')->get();
            $subjects = Subject::all();
            $students = Student::with('user')->get();

            // Calculate statistics if specific exam and subject are selected
            $statistics = null;
            if ($request->filled('exam_id') && $request->filled('subject_id')) {
                $statistics = $this->resultRepository->getStatistics(
                    $request->exam_id, 
                    $request->subject_id
                );
                $statistics['exam'] = Exam::find($request->exam_id);
                $statistics['subject'] = Subject::find($request->subject_id);
            }

            return view('results.index', compact(
                'results', 
                'exams', 
                'classes', 
                'subjects', 
                'students',
                'statistics'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to load results list', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load results. Please try again.');
        }
    }

    /**
     * Show the form for creating bulk results.
     */
    public function createBulk($exam_id, $class_id, $subject_id)
    {
        try {
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
                return redirect()->route('results.index', [
                    'exam_id' => $exam->id, 
                    'subject_id' => $subject->id
                ])->with('error', 'Results for this exam have already been published and cannot be modified.');
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
            
            // Get existing results
            $existingResults = $this->resultRepository->getExistingResults($exam_id, $subject_id);

            return view('results.create-bulk', compact(
                'exam', 
                'class', 
                'subject', 
                'students', 
                'subjects', 
                'existingResults', 
                'gradingScales'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to load bulk result form', [
                'exam_id' => $exam_id,
                'class_id' => $class_id,
                'subject_id' => $subject_id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load result entry form. Please try again.');
        }
    }

    /**
     * Store multiple results in storage.
     */
    public function storeBulk(StoreResultRequest $request)
    {
        try {
            $validated = $request->validated();
            
            $studentMarks = [];
            foreach ($validated['student_ids'] as $student_id) {
                if (isset($validated['marks_obtained'][$student_id])) {
                    $studentMarks[$student_id] = [
                        'marks_obtained' => $validated['marks_obtained'][$student_id],
                        'remarks' => $validated['remarks'][$student_id] ?? null,
                    ];
                }
            }

            $count = $this->resultRepository->storeBulk(
                $validated['exam_id'],
                $validated['subject_id'],
                $studentMarks,
                Auth::id()
            );

            Log::info('Bulk results saved successfully', [
                'exam_id' => $validated['exam_id'],
                'subject_id' => $validated['subject_id'],
                'count' => $count,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('exams.show', $validated['exam_id'])
                ->with('success', "Results saved successfully. {$count} records processed.");
        } catch (\Exception $e) {
            Log::error('Failed to save bulk results', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to save results. Please try again.');
        }
    }

    /**
     * Display the specified result.
     */
    public function show(Result $result)
    {
        try {
            $result->load(['student.user', 'exam', 'subject', 'createdBy']);
            return view('results.show', compact('result'));
        } catch (\Exception $e) {
            Log::error('Failed to load result details', [
                'result_id' => $result->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load result details. Please try again.');
        }
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

        try {
            $data = [
                'marks_obtained' => $request->marks_obtained,
                'remarks' => $request->remarks,
            ];

            $this->resultRepository->update($result, $data);

            Log::info('Result updated successfully', [
                'result_id' => $result->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('results.index', [
                'exam_id' => $result->exam_id, 
                'subject_id' => $result->subject_id
            ])->with('success', 'Result updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update result', [
                'result_id' => $result->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->withInput()->with('error', 'Failed to update result. Please try again.');
        }
    }

    /**
     * Remove the specified result from storage.
     */
    public function destroy(Result $result)
    {
        try {
            $this->resultRepository->delete($result);

            Log::info('Result deleted successfully', [
                'result_id' => $result->id,
                'user_id' => Auth::id(),
            ]);

            return back()->with('success', 'Result deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete result', [
                'result_id' => $result->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            // Check if error is due to published results
            if (str_contains($e->getMessage(), 'published')) {
                return back()->with('error', 'Results for this exam have already been published and cannot be deleted.');
            }

            return back()->with('error', 'Failed to delete result. Please try again.');
        }
    }

    /**
     * Display the student's own results.
     */
    public function myResults(Request $request)
    {
        try {
            $student = Auth::user()->student;
            
            if (!$student) {
                abort(403, 'You are not registered as a student.');
            }

            $results = $this->resultRepository->getStudentResults($student->id, $request->exam_id);

            return view('results.my-results', compact('results', 'student'));
        } catch (\Exception $e) {
            Log::error('Failed to load student results', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load your results. Please try again.');
        }
    }
    
    /**
     * Generate PDF mark sheet for a student.
     */
    public function generateMarkSheet($examId, $studentId)
    {
        try {
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
            $pdf = Pdf::loadView('results.mark-sheet', compact(
                'exam',
                'student',
                'results',
                'overallResult',
                'position',
                'gradingScales'
            ));

            Log::info('Mark sheet generated', [
                'exam_id' => $examId,
                'student_id' => $studentId,
                'user_id' => Auth::id(),
            ]);
            
            return $pdf->download('mark_sheet_' . $student->student_id . '_' . $exam->name . '.pdf');
        } catch (\Exception $e) {
            Log::error('Failed to generate mark sheet', [
                'exam_id' => $examId,
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to generate mark sheet. Please try again.');
        }
    }
    
    /**
     * View class result summary.
     */
    public function classResultSummary(Exam $exam)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Failed to load class result summary', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load class result summary. Please try again.');
        }
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
        try {
            $validated = $request->validated();
            $examId = $validated['exam_id'];
            $classId = $validated['class_id'];
            $marks = $validated['marks'];

            // Get all subjects for the class to validate subject IDs
            $subjects = Subject::where('class_id', $classId)->get();
            $validSubjectIds = $subjects->pluck('id')->all();

            // Build per-subject map keyed by subjectId => [studentId => { marks_obtained, remarks }]
            $bySubject = [];
            $totalProcessed = 0;

            foreach ($marks as $studentId => $subjectMarks) {
                foreach ($subjectMarks as $subjectId => $marksObtained) {
                    if ($marksObtained === '' || $marksObtained === null) {
                        continue; // Skip empty marks
                    }

                    // Validate subject and student belong to class/are valid
                    if (!in_array((int)$subjectId, $validSubjectIds, true)) {
                        continue;
                    }
                    $student = Student::find($studentId);
                    if (!$student || (int)$student->class_id !== (int)$classId) {
                        continue;
                    }

                    // Build payload for repository (marks_obtained and optional remarks)
                    if (!isset($bySubject[$subjectId])) {
                        $bySubject[$subjectId] = [];
                    }
                    $bySubject[$subjectId][$studentId] = [
                        'marks_obtained' => (float) $marksObtained,
                        'remarks' => null,
                    ];
                }
            }

            if (empty($bySubject)) {
                return back()->with('error', 'No valid marks to save.');
            }

            // Store marks using repository one call per subject
            foreach ($bySubject as $subjectId => $studentMap) {
                $count = $this->resultRepository->storeBulk(
                    $examId,
                    $subjectId,
                    $studentMap,
                    Auth::id()
                );
                $totalProcessed += (int) $count;
            }

            Log::info('Marks saved successfully', [
                'exam_id' => $examId,
                'count' => $totalProcessed,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->with('success', "Marks saved successfully. {$totalProcessed} records processed.");
        } catch (\Exception $e) {
            Log::error('Failed to save marks', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to save marks. Please try again.');
        }
    }

    /**
     * Generate PDF class result summary.
     */
    public function generateClassResultSummary(Exam $exam)
    {
        try {
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
            $pdf = Pdf::loadView('results.class-summary-pdf', compact(
                'exam',
                'rankList',
                'subjects',
                'gradingScales'
            ));

            Log::info('Class result summary PDF generated', [
                'exam_id' => $exam->id,
                'user_id' => Auth::id(),
            ]);
            
            return $pdf->download('class_result_summary_' . $exam->name . '.pdf');
        } catch (\Exception $e) {
            Log::error('Failed to generate class result summary PDF', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }
}
