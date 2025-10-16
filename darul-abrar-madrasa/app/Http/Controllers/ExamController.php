<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Models\ClassRoom;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Notification;
use App\Repositories\ExamRepository;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExamController extends Controller
{
    protected $examRepository;

    public function __construct(ExamRepository $examRepository)
    {
        $this->examRepository = $examRepository;
    }

    /**
     * Display a listing of the exams.
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->search,
                'class_id' => $request->class_id,
                'status' => $request->status,
                'result_status' => $request->result_status,
            ];

            $exams = $this->examRepository->getAllWithFilters($filters, 15);
            $classes = ClassRoom::with('department')->get();

            return view('exams.index', compact('exams', 'classes'));
        } catch (\Exception $e) {
            Log::error('Failed to load exams list', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load exams. Please try again.');
        }
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
    public function store(StoreExamRequest $request)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
            $data['is_result_published'] = $request->has('is_result_published') ? $request->boolean('is_result_published') : false;

            $exam = $this->examRepository->create($data);

            Log::info('Exam created successfully', [
                'exam_id' => $exam->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('exams.show', $exam->id)
                ->with('success', 'Exam created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create exam', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to create exam. Please try again.');
        }
    }

    /**
     * Display the specified exam.
     */
    public function show(Exam $exam)
    {
        try {
            $examWithStats = $this->examRepository->getWithStatistics($exam->id);
            
            // Get subjects for this class
            $subjects = Subject::where('class_id', $exam->class_id)
                ->with('teacher.user')
                ->get();

            // Map to variables expected by the blade view
            $exam = $examWithStats;
            $totalStudents = $exam->statistics['totalStudents'] ?? 0;
            $passedStudents = $exam->statistics['passedStudents'] ?? 0;
            $failedStudents = $exam->statistics['failedStudents'] ?? 0;
            $passRate = $exam->statistics['passRate'] ?? 0;

            return view('exams.show', compact('exam', 'subjects', 'totalStudents', 'passedStudents', 'failedStudents', 'passRate'));
        } catch (\Exception $e) {
            Log::error('Failed to load exam details', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load exam details. Please try again.');
        }
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
    public function update(UpdateExamRequest $request, Exam $exam)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
            $data['is_result_published'] = $request->has('is_result_published') ? $request->boolean('is_result_published') : false;

            $updatedExam = $this->examRepository->update($exam, $data);

            Log::info('Exam updated successfully', [
                'exam_id' => $exam->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('exams.show', $exam->id)
                ->with('success', 'Exam updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update exam', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to update exam. Please try again.');
        }
    }

    /**
     * Remove the specified exam from storage.
     */
    public function destroy(Exam $exam)
    {
        try {
            $this->examRepository->delete($exam);

            Log::info('Exam deleted successfully', [
                'exam_id' => $exam->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('exams.index')
                ->with('success', 'Exam deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete exam', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            // Check if error is due to existing results
            if (str_contains($e->getMessage(), 'results')) {
                return back()->with('error', 'Cannot delete exam because it has associated results. Delete the results first.');
            }

            return back()->with('error', 'Failed to delete exam. Please try again.');
        }
    }

    /**
     * Return exams available for marks entry (completed and not yet published) for a given class.
     * Expects: class_id (required), subject_id (optional).
     * Response: JSON { exams: [ {id, name, start_date, end_date} ] }
     */
    public function getExamsForMarksEntry(Request $request)
    {
        try {
            $classId = (int) $request->get('class_id');
            $subjectId = $request->get('subject_id'); // reserved for possible future filtering

            if (!$classId) {
                return response()->json(['exams' => [], 'message' => 'class_id is required'], 422);
            }

            $exams = Exam::where('class_id', $classId)
                ->where('end_date', '<', now())
                ->where('is_result_published', false)
                ->orderByDesc('end_date')
                ->get(['id', 'name', 'start_date', 'end_date']);

            return response()->json(['exams' => $exams]);
        } catch (\Exception $e) {
            Log::error('Failed to get exams for marks entry', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'class_id' => $request->get('class_id'),
                'subject_id' => $request->get('subject_id'),
            ]);

            return response()->json(['exams' => [], 'message' => 'Failed to fetch exams'], 500);
        }
    }

    /**
     * Publish the results for the specified exam.
     */
    public function publishResults(Exam $exam)
    {
        try {
            $result = $this->examRepository->publishResults($exam);

            if ($result['success']) {
                Log::info('Exam results published successfully', [
                    'exam_id' => $exam->id,
                    'user_id' => Auth::id(),
                ]);

                // Send result publication notifications to guardians
                try {
                    $notificationService = app(NotificationService::class);
                    $notificationsSent = 0;

                    // Get all students in the exam's class
                    $students = Student::where('class_id', $exam->class_id)
                        ->with(['guardians', 'results' => function ($query) use ($exam) {
                            $query->where('exam_id', $exam->id);
                        }])
                        ->get();

                    foreach ($students as $student) {
                        // Get student's result for this exam
                        $studentResult = $student->results->first();
                        
                        if ($studentResult) {
                            // Get guardians with notifications enabled
                            $guardians = $student->guardians()
                                ->wherePivot('receive_notifications', true)
                                ->get();

                            foreach ($guardians as $guardian) {
                                $data = [
                                    'student_name' => $student->name,
                                    'guardian_name' => $guardian->name,
                                    'exam_name' => $exam->name,
                                    'class_name' => $exam->class->name ?? 'N/A',
                                    'gpa' => number_format($studentResult->gpa ?? 0, 2),
                                    'status' => $studentResult->status ?? 'N/A',
                                    'total_marks' => $studentResult->total_marks ?? 0,
                                    'obtained_marks' => $studentResult->obtained_marks ?? 0,
                                ];

                                $notificationId = $notificationService->sendNotification(
                                    Notification::TYPE_RESULT_PUBLISHED,
                                    $guardian->id,
                                    'guardian',
                                    $data
                                );

                                if ($notificationId) {
                                    $notificationsSent++;
                                }
                            }
                        }
                    }

                    Log::info('Result publication notifications sent', [
                        'exam_id' => $exam->id,
                        'notifications_sent' => $notificationsSent,
                    ]);
                } catch (\Exception $notificationError) {
                    // Log notification error but don't fail the result publication
                    Log::error('Failed to send result publication notifications', [
                        'exam_id' => $exam->id,
                        'error' => $notificationError->getMessage(),
                    ]);
                }

                return redirect()->route('exams.show', $exam->id)
                    ->with('success', 'Exam results published successfully.');
            } else {
                Log::warning('Failed to publish exam results', [
                    'exam_id' => $exam->id,
                    'reason' => $result['message'],
                    'user_id' => Auth::id(),
                ]);

                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to publish exam results', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to publish results. Please try again.');
        }
    }
}
