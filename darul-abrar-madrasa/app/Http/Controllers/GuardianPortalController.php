<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\Fee;
use App\Models\Notice;
use App\Models\Result;
use App\Models\StudyMaterial;
use App\Models\NotificationPreference;
use App\Models\Notification;
use App\Repositories\ResultRepository;
use App\Repositories\AttendanceRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GuardianPortalController extends Controller
{
    /**
     * Guardian dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $guardian = Guardian::with(['user', 'students.user', 'students.class'])->where('user_id', $user->id)->first();

        if (!$guardian) {
            return redirect()->route('dashboard')->with('error', 'Guardian profile not found.');
        }

        $students = $guardian->students()->with(['user', 'class'])->get();
        $totalChildren = $students->count();

        // Total pending fees where guardian has financial responsibility
        $totalPendingFees = 0.0;
        foreach ($students as $student) {
            $link = $guardian->students()->where('students.id', $student->id)->first();
            $financial = $link ? (bool) $link->pivot->financial_responsibility : false;
            if ($financial) {
                $totalPendingFees += (float) $student->getPendingFeesAmount();
            }
        }

        // Average attendance across children (best-effort if helper exists)
        $attendanceSum = 0.0;
        foreach ($students as $s) {
            if (method_exists($s, 'getAttendanceRate')) {
                $attendanceSum += (float) $s->getAttendanceRate();
            }
        }
        $averageAttendance = $totalChildren > 0 ? round($attendanceSum / $totalChildren, 2) : 0.0;

        // Upcoming exams for all children's classes
        $classIds = $students->pluck('class_id')->filter()->unique()->values()->all();
        $upcomingExams = Exam::with('class')
            ->whereIn('class_id', $classIds ?: [-1])
            ->whereDate('start_date', '>=', Carbon::now())
            ->orderBy('start_date', 'asc')
            ->take(10)
            ->get();

        // Recent notices for guardians/all
        $recentNotices = Notice::where('is_active', 1)
            ->where('publish_date', '<=', Carbon::now())
            ->where(function ($q) {
                $q->where('notice_for', 'guardians')->orWhere('notice_for', 'all');
            })
            ->where(function ($e) {
                $e->whereNull('expiry_date')->orWhere('expiry_date', '>=', Carbon::now());
            })
            ->latest()
            ->take(6)
            ->get();

        // Simple chart datasets
        $attendanceTrend = [
            'labels' => $students->map(fn ($s) => optional($s->user)->name ?? ('Student #' . $s->id))->values()->all(),
            'data' => $students->map(fn ($s) => method_exists($s, 'getAttendanceRate') ? (float) $s->getAttendanceRate() : 0.0)->values()->all(),
        ];

        $feePaymentStatus = [
            'labels' => $students->map(fn ($s) => optional($s->user)->name ?? ('Student #' . $s->id))->values()->all(),
            'data' => $students->map(function ($s) {
                $total = (float) $s->fees()->sum('amount');
                $paid = (float) $s->fees()->sum('paid_amount');
                return $total > 0 ? round(($paid / $total) * 100, 2) : 0;
            })->values()->all(),
        ];

        // Render dashboard view (to be created)
        return view('dashboard.guardian', compact(
            'guardian',
            'students',
            'totalChildren',
            'totalPendingFees',
            'averageAttendance',
            'upcomingExams',
            'recentNotices',
            'attendanceTrend',
            'feePaymentStatus'
        ));
    }

    /**
     * List all children (linked students)
     */
    public function children()
    {
        $guardian = $this->getGuardianOrAbort();
        $students = $guardian->students()->with(['user', 'class'])->get();

        return view('guardian.children', compact('guardian', 'students'));
    }

    /**
     * Child profile
     */
    public function childProfile(Student $student)
    {
        $guardian = $this->getGuardianOrAbort();
        $this->authorizeGuardianForStudent($guardian, $student);

        $student->load(['user', 'class']);
        return view('guardian.child-profile', compact('guardian', 'student'));
    }

    /**
     * Child attendance
     */
    public function childAttendance(Student $student, Request $request)
    {
        $guardian = $this->getGuardianOrAbort();
        $this->authorizeGuardianForStudent($guardian, $student);

        $query = Attendance::where('student_id', $student->id);
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $records = $query->orderBy('date', 'desc')->paginate(30);
        $total = Attendance::where('student_id', $student->id)->count();
        $present = Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $attendancePercentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;

        return view('guardian.child-attendance', compact('guardian', 'student', 'records', 'attendancePercentage'));
    }

    /**
     * Child results
     */
    public function childResults(Student $student)
    {
        $guardian = $this->getGuardianOrAbort();
        $this->authorizeGuardianForStudent($guardian, $student);

        $results = Result::with(['exam', 'subject'])
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('guardian.child-results', compact('guardian', 'student', 'results'));
    }

    /**
     * Child fees
     */
    public function childFees(Student $student)
    {
        $guardian = $this->getGuardianOrAbort();
        $this->authorizeGuardianForStudent($guardian, $student, requireFinancial: true);

        $fees = Fee::where('student_id', $student->id)
            ->orderBy('due_date', 'asc')
            ->get();

        $totalPending = $fees->sum(function ($f) {
            $net = (float) ($f->net_amount ?? $f->amount);
            return max(0, $net - (float) ($f->paid_amount ?? 0));
        });

        return view('guardian.child-fees', compact('guardian', 'student', 'fees', 'totalPending'));
    }

    /**
     * All fees across financial-responsibility children
     */
    public function allFees()
    {
        $guardian = $this->getGuardianOrAbort();

        $students = $guardian->students()->with('user')->get();

        $feesByStudent = [];
        $totalPending = 0.0;

        foreach ($students as $student) {
            $link = $guardian->students()->where('students.id', $student->id)->first();
            if ($link && (bool) $link->pivot->financial_responsibility) {
                $fees = Fee::where('student_id', $student->id)->orderBy('due_date')->get();
                $feesByStudent[$student->id] = $fees;
                $totalPending += $fees->sum(function ($f) {
                    $net = (float) ($f->net_amount ?? $f->amount);
                    return max(0, $net - (float) ($f->paid_amount ?? 0));
                });
            }
        }

        return view('guardian.all-fees', compact('guardian', 'students', 'feesByStudent', 'totalPending'));
    }

    /**
     * Pay fee page (initiate)
     */
    public function payFee(Fee $fee)
    {
        $guardian = $this->getGuardianOrAbort();
        $student = Student::findOrFail($fee->student_id);
        $this->authorizeGuardianForStudent($guardian, $student, requireFinancial: true);

        return view('guardian.pay-fee', compact('guardian', 'student', 'fee'));
    }

    /**
     * Process payment (placeholder integration)
     */
    public function processPayment(Request $request, Fee $fee)
    {
        // Feature flag: guardian payments must be explicitly enabled
        abort_unless(config('payments.guardian_portal_enabled'), 403, 'Guardian payments are currently disabled.');

        $guardian = $this->getGuardianOrAbort();
        $student = Student::findOrFail($fee->student_id);
        $this->authorizeGuardianForStudent($guardian, $student, requireFinancial: true);

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        // Validate not exceeding remaining/net amount
        $remaining = (float) ($fee->remaining_amount ?? max(0, (float) ($fee->net_amount ?? $fee->amount) - (float) ($fee->paid_amount ?? 0)));
        $amount = (float) $data['amount'];
        if ($amount > $remaining + 0.0001) {
            return back()
                ->withInput()
                ->with('error', 'Payment amount exceeds the remaining due.');
        }

        // Placeholder behavior: directly apply payment (to be replaced by gateway callback)
        $newPaid = min((float) ($fee->net_amount ?? $fee->amount), (float) ($fee->paid_amount ?? 0) + $amount);
        $status = $newPaid >= (float) ($fee->net_amount ?? $fee->amount) ? 'paid' : 'partial';

        $fee->update([
            'paid_amount' => $newPaid,
            'status' => $status,
            'payment_method' => $data['payment_method'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'payment_date' => now(),
            'collected_by' => Auth::id(),
        ]);

        return redirect()->route('guardian.fees')->with('success', 'Payment recorded successfully.');
    }

    /**
     * Study materials for child's class
     */
    public function studyMaterials(Student $student, Request $request)
    {
        $guardian = $this->getGuardianOrAbort();
        $this->authorizeGuardianForStudent($guardian, $student);

        $query = StudyMaterial::with(['teacher', 'subject'])
            ->where('class_id', $student->class_id)
            ->where('is_published', true);

        if ($request->filled('content_type')) {
            $query->where('content_type', $request->input('content_type'));
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        $studyMaterials = $query->latest()->paginate(12);

        return view('guardian.study-materials', compact('guardian', 'student', 'studyMaterials'));
    }

    /**
     * Notices for guardians
     */
    public function notices()
    {
        $guardian = $this->getGuardianOrAbort();

        $notices = Notice::where('is_active', 1)
            ->where('publish_date', '<=', Carbon::now())
            ->where(function ($q) {
                $q->where('notice_for', 'guardians')->orWhere('notice_for', 'all');
            })
            ->where(function ($e) {
                $e->whereNull('expiry_date')->orWhere('expiry_date', '>=', Carbon::now());
            })
            ->latest()
            ->paginate(12);

        return view('guardian.notices', compact('guardian', 'notices'));
    }

    /**
     * Performance report for a child
     */
    public function performanceReport(Student $student, Request $request, ResultRepository $resultRepo, AttendanceRepository $attendanceRepo)
    {
        try {
            $guardian = $this->getGuardianOrAbort();
            $this->authorizeGuardianForStudent($guardian, $student);

            // Get report data
            $data = $this->getPerformanceReportData($student, $request, $resultRepo, $attendanceRepo);

            return view('guardian.performance-report', array_merge($data, [
                'guardian' => $guardian,
                'student' => $student,
            ]));
        } catch (\Exception $e) {
            Log::error('Performance report error', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to generate performance report: ' . $e->getMessage());
        }
    }

    /**
     * Download performance report as PDF
     */
    public function downloadPerformanceReport(Student $student, Request $request, ResultRepository $resultRepo, AttendanceRepository $attendanceRepo)
    {
        try {
            $guardian = $this->getGuardianOrAbort();
            $this->authorizeGuardianForStudent($guardian, $student);

            // Get report data
            $data = $this->getPerformanceReportData($student, $request, $resultRepo, $attendanceRepo);

            // Generate PDF
            $pdf = Pdf::loadView('guardian.performance-report-pdf', array_merge($data, [
                'guardian' => $guardian,
                'student' => $student,
            ]));
            $pdf->setPaper('A4', 'portrait');

            $filename = 'performance-report-' . str_replace(' ', '-', $student->user->name ?? 'student') . '-' . now()->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Performance report PDF download error', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to download performance report: ' . $e->getMessage());
        }
    }

    /**
     * Email performance report
     */
    public function emailPerformanceReport(Student $student, Request $request, ResultRepository $resultRepo, AttendanceRepository $attendanceRepo)
    {
        try {
            $guardian = $this->getGuardianOrAbort();
            $this->authorizeGuardianForStudent($guardian, $student);

            // Validate email
            $validated = $request->validate([
                'email' => 'required|email',
            ]);

            // Get report data
            $data = $this->getPerformanceReportData($student, $request, $resultRepo, $attendanceRepo);

            // Generate PDF in memory
            $pdf = Pdf::loadView('guardian.performance-report-pdf', array_merge($data, [
                'guardian' => $guardian,
                'student' => $student,
            ]));
            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();

            // Send email
            $emailData = array_merge($data, [
                'guardian' => $guardian,
                'student' => $student,
            ]);

            Mail::send('emails.performance-report', $emailData, function ($message) use ($validated, $student, $pdfContent, $data) {
                $message->to($validated['email'])
                    ->subject('Performance Report for ' . ($student->user->name ?? 'Student') . ' - ' . $data['dateRange']['start']->format('M d, Y') . ' to ' . $data['dateRange']['end']->format('M d, Y'))
                    ->attachData($pdfContent, 'performance-report-' . str_replace(' ', '-', $student->user->name ?? 'student') . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });

            Log::info('Performance report emailed', [
                'student_id' => $student->id,
                'email' => $validated['email'],
                'guardian_id' => $guardian->id,
            ]);

            return redirect()->back()->with('success', 'Performance report has been sent to ' . $validated['email']);
        } catch (\Exception $e) {
            Log::error('Performance report email error', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to email performance report: ' . $e->getMessage());
        }
    }

    /**
     * Helpers
     */
    private function getGuardianOrAbort(): Guardian
    {
        $user = Auth::user();
        $guardian = Guardian::where('user_id', $user->id)->first();
        if (!$guardian) {
            abort(403, 'Guardian profile not found.');
        }
        return $guardian;
    }

    private function authorizeGuardianForStudent(Guardian $guardian, Student $student, bool $requireFinancial = false): void
    {
        $link = $guardian->students()->where('students.id', $student->id)->first();
        if (!$link) {
            abort(403, 'You are not linked to this student.');
        }
        if ($requireFinancial && !(bool) $link->pivot->financial_responsibility) {
            abort(403, 'You do not have financial responsibility for this student.');
        }
    }

    /**
     * Get performance report data
     */
    private function getPerformanceReportData(Student $student, Request $request, ResultRepository $resultRepo, AttendanceRepository $attendanceRepo): array
    {
        // Get filters
        $reportType = $request->input('report_type', 'term');
        
        if ($reportType === 'annual') {
            $year = $request->input('year', now()->year);
            $termStart = Carbon::create($year, 1, 1)->startOfDay();
            $termEnd = Carbon::create($year, 12, 31)->endOfDay();
        } else {
            $termStart = $request->input('term_start') 
                ? Carbon::parse($request->input('term_start'))->startOfDay()
                : Carbon::now()->subMonths(3)->startOfDay();
            $termEnd = $request->input('term_end')
                ? Carbon::parse($request->input('term_end'))->endOfDay()
                : Carbon::now()->endOfDay();
        }

        // Load student relationships
        $student->load(['user', 'class.department', 'guardians']);

        // Get academic performance
        $examResults = $resultRepo->getStudentResults($student->id);
        
        // Filter by date range
        $filteredResults = $examResults->filter(function ($examData) use ($termStart, $termEnd) {
            $examEndDate = $examData['exam']->end_date ?? $examData['exam']->start_date;
            return $examEndDate >= $termStart && $examEndDate <= $termEnd;
        });

        // Calculate overall statistics
        $totalExams = $filteredResults->count();
        $averageGpa = $filteredResults->avg('summary.averageGpa') ?? 0;
        $passedExams = $filteredResults->where('summary.status', 'Passed')->count();
        $passRate = $totalExams > 0 ? ($passedExams / $totalExams) * 100 : 0;

        // Find best and weakest subjects
        $allResults = $filteredResults->flatMap(fn($exam) => $exam['results']);
        $subjectPerformance = $allResults->groupBy('subject_id')->map(function ($results) {
            return [
                'subject' => $results->first()->subject,
                'average_marks' => $results->avg('marks_obtained'),
                'average_gpa' => $results->avg('gpa_point'),
            ];
        })->sortByDesc('average_gpa');

        $bestSubject = $subjectPerformance->first();
        $weakestSubject = $subjectPerformance->last();

        $academicPerformance = [
            'examResults' => $filteredResults,
            'totalExams' => $totalExams,
            'averageGpa' => round($averageGpa, 2),
            'passRate' => round($passRate, 2),
            'bestSubject' => $bestSubject,
            'weakestSubject' => $weakestSubject,
            'subjectPerformance' => $subjectPerformance,
        ];

        // Get attendance data
        $attendanceStats = $attendanceRepo->getStudentStats($student->id);
        
        // Get attendance records for date range
        $attendanceRecords = Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$termStart, $termEnd])
            ->orderBy('date', 'desc')
            ->get();

        $termTotalDays = $attendanceRecords->count();
        $termPresentDays = $attendanceRecords->where('status', 'present')->count();
        $termAbsentDays = $attendanceRecords->where('status', 'absent')->count();
        $termLateDays = $attendanceRecords->where('status', 'late')->count();
        $termLeaveDays = $attendanceRecords->where('status', 'leave')->count();
        $termHalfDays = $attendanceRecords->where('status', 'half_day')->count();
        $termAttendanceRate = $termTotalDays > 0 ? ($termPresentDays / $termTotalDays) * 100 : 0;

        $attendanceSummary = [
            'overall' => $attendanceStats,
            'term' => [
                'totalDays' => $termTotalDays,
                'presentDays' => $termPresentDays,
                'absentDays' => $termAbsentDays,
                'lateDays' => $termLateDays,
                'leaveDays' => $termLeaveDays,
                'halfDays' => $termHalfDays,
                'attendanceRate' => round($termAttendanceRate, 2),
            ],
            'records' => $attendanceRecords,
        ];

        // Get fee status
        $fees = Fee::where('student_id', $student->id)
            ->whereBetween('due_date', [$termStart, $termEnd])
            ->get();

        $totalFees = $fees->sum(fn($f) => (float)($f->net_amount ?? $f->amount));
        $paidAmount = $fees->sum(fn($f) => (float)($f->paid_amount ?? 0));
        $pendingAmount = max(0, $totalFees - $paidAmount);

        $feeStatus = [
            'fees' => $fees,
            'totalFees' => $totalFees,
            'paidAmount' => $paidAmount,
            'pendingAmount' => $pendingAmount,
            'paymentStatus' => $pendingAmount > 0 ? 'Pending' : 'Paid',
        ];

        // Get teacher remarks
        $teacherRemarks = $allResults->filter(fn($r) => !empty($r->remarks))
            ->groupBy('subject_id')
            ->map(function ($results) {
                return $results->map(function ($result) {
                    return [
                        'subject' => $result->subject->name ?? 'N/A',
                        'exam' => $result->exam->name ?? 'N/A',
                        'remarks' => $result->remarks,
                        'date' => $result->created_at,
                    ];
                });
            })
            ->flatten(1);

        // Generate recommendations
        $recommendations = [];

        if ($termAttendanceRate < 75) {
            $recommendations[] = [
                'priority' => 'High',
                'type' => 'attendance',
                'title' => 'Improve Attendance',
                'description' => 'Attendance rate is below 75%. Regular attendance is crucial for academic success.',
                'action' => 'Ensure the student attends classes regularly.',
            ];
        }

        if ($averageGpa < 2.5) {
            $recommendations[] = [
                'priority' => 'High',
                'type' => 'academic',
                'title' => 'Academic Support Needed',
                'description' => 'Average GPA is below 2.5. Consider additional academic support.',
                'action' => 'Arrange extra tutoring or study sessions.',
            ];
        }

        if ($pendingAmount > 0) {
            $recommendations[] = [
                'priority' => 'Medium',
                'type' => 'financial',
                'title' => 'Pending Fees',
                'description' => 'There are pending fees of ' . number_format($pendingAmount, 2) . ' BDT.',
                'action' => 'Please clear pending fees at the earliest.',
            ];
        }

        if ($weakestSubject && isset($weakestSubject['average_gpa']) && $weakestSubject['average_gpa'] < 2.0) {
            $recommendations[] = [
                'priority' => 'Medium',
                'type' => 'subject',
                'title' => 'Focus on ' . ($weakestSubject['subject']->name ?? 'Weak Subject'),
                'description' => 'Performance in this subject needs improvement.',
                'action' => 'Provide additional practice and support for this subject.',
            ];
        }

        if ($averageGpa >= 3.5 && $termAttendanceRate >= 90) {
            $recommendations[] = [
                'priority' => 'Low',
                'type' => 'positive',
                'title' => 'Excellent Performance',
                'description' => 'The student is performing excellently in both academics and attendance.',
                'action' => 'Keep up the good work and maintain consistency.',
            ];
        }

        return [
            'reportType' => $reportType,
            'dateRange' => [
                'start' => $termStart,
                'end' => $termEnd,
            ],
            'academicPerformance' => $academicPerformance,
            'attendanceSummary' => $attendanceSummary,
            'feeStatus' => $feeStatus,
            'teacherRemarks' => $teacherRemarks,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Show notification preferences
     */
    public function notificationPreferences()
    {
        try {
            $guardian = $this->getGuardianOrAbort();

            // Get or create preferences for all notification types
            $notificationTypes = [
                Notification::TYPE_LOW_ATTENDANCE => 'Low Attendance Alert',
                Notification::TYPE_POOR_PERFORMANCE => 'Poor Performance Alert',
                Notification::TYPE_FEE_DUE => 'Fee Due Reminder',
                Notification::TYPE_EXAM_SCHEDULE => 'Exam Schedule Notification',
                Notification::TYPE_RESULT_PUBLISHED => 'Result Publication Alert',
            ];

            $preferences = [];
            foreach ($notificationTypes as $type => $label) {
                $preference = NotificationPreference::firstOrCreate(
                    [
                        'guardian_id' => $guardian->id,
                        'notification_type' => $type,
                    ],
                    [
                        'email_enabled' => true,
                        'sms_enabled' => true,
                    ]
                );

                $preferences[$type] = [
                    'label' => $label,
                    'preference' => $preference,
                ];
            }

            return view('guardian.notification-preferences', compact('guardian', 'preferences', 'notificationTypes'));
        } catch (\Exception $e) {
            Log::error('Failed to load notification preferences', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load notification preferences. Please try again.');
        }
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(Request $request)
    {
        try {
            $guardian = $this->getGuardianOrAbort();

            $validated = $request->validate([
                'preferences' => 'required|array',
                'preferences.*.email_enabled' => 'boolean',
                'preferences.*.sms_enabled' => 'boolean',
            ]);

            foreach ($validated['preferences'] as $type => $settings) {
                NotificationPreference::updateOrCreate(
                    [
                        'guardian_id' => $guardian->id,
                        'notification_type' => $type,
                    ],
                    [
                        'email_enabled' => $settings['email_enabled'] ?? false,
                        'sms_enabled' => $settings['sms_enabled'] ?? false,
                    ]
                );
            }

            Log::info('Notification preferences updated', [
                'guardian_id' => $guardian->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('guardian.notification-preferences')
                ->with('success', 'Notification preferences updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update notification preferences', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->withInput()->with('error', 'Failed to update preferences. Please try again.');
        }
    }
}
