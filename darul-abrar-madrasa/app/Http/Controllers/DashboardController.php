<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Fee;
use App\Models\Notice;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Guardian;
use App\Models\Accountant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $this->adminDashboard();
        } elseif (method_exists($user, 'isTeacher') && $user->isTeacher()) {
            return $this->teacherDashboard();
        } elseif (method_exists($user, 'isStudent') && $user->isStudent()) {
            return $this->studentDashboard();
        } elseif (method_exists($user, 'isGuardian') && $user->isGuardian()) {
            return $this->guardianDashboard();
        } elseif (method_exists($user, 'isAccountant') && $user->isAccountant()) {
            return $this->accountantDashboard();
        } else {
            return $this->staffDashboard();
        }
    }

    /**
     * Display the admin dashboard with enhanced analytics.
     */
    private function adminDashboard()
    {
        // High-level counts
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = ClassRoom::count();
        $totalDepartments = Department::count();
        $totalSubjects = Subject::count();
        $totalUsers = User::count();

        // Recent fee collections
        $recentFees = Fee::with(['student.user'])
            ->latest()
            ->take(5)
            ->get();

        // Collected and pending fees
        $totalFeesCollected = (float) Fee::where('status', 'paid')->sum('paid_amount');

        // Pending fees: outstanding balance (unpaid full amount + partial remaining)
        $pendingFees = (float) DB::table('fees')
            ->selectRaw('COALESCE(SUM(CASE WHEN status = "unpaid" THEN amount WHEN status = "partial" THEN amount - paid_amount ELSE 0 END),0) as due')
            ->value('due');

        // Upcoming exams (fallback if no scope exists)
        $upcomingExams = Exam::with('class')
            ->whereDate('start_date', '>', Carbon::now())
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        // Recent active notices
        $recentNotices = Notice::with('publishedBy')
            ->where(function ($q) {
                // If scopes exist, prefer them; otherwise emulate published & notExpired
                $q->where('is_active', 1)
                  ->where('publish_date', '<=', Carbon::now())
                  ->where(function ($e) {
                      $e->whereNull('expiry_date')
                        ->orWhere('expiry_date', '>=', Carbon::now());
                  });
            })
            ->latest()
            ->take(5)
            ->get();

        // Last 6 months labels
        $months = collect(range(5, 0))->map(function ($i) {
            return Carbon::now()->subMonths($i)->startOfMonth();
        });

        // Fee collection (paid_amount) by month (last 6 months)
        $feeCollectionByMonth = Cache::remember('dashboard_admin_fee_collection_6m', 300, function () use ($months) {
            $sums = DB::table('fees')
                ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m-01") as month_start, SUM(paid_amount) as total')
                ->where('status', 'paid')
                ->whereBetween('payment_date', [$months->first()->copy()->startOfMonth(), $months->last()->copy()->endOfMonth()])
                ->groupBy('month_start')
                ->pluck('total', 'month_start');

            $labels = $months->map(fn ($m) => $m->format('F'))->toArray();
            $data = $months->map(function ($m) use ($sums) {
                $key = $m->format('Y-m-01');
                return (float) ($sums[$key] ?? 0);
            })->toArray();

            return ['labels' => $labels, 'data' => $data];
        });

        // Department-wise student distribution
        $departmentDistribution = Cache::remember('dashboard_admin_dept_distribution', 300, function () {
            // Join students -> classes -> departments
            $rows = DB::table('students as s')
                ->join('classes as c', 'c.id', '=', 's.class_id')
                ->join('departments as d', 'd.id', '=', 'c.department_id')
                ->selectRaw('d.name as dept, COUNT(s.id) as total')
                ->groupBy('d.name')
                ->orderBy('d.name')
                ->get();

            $labels = $rows->pluck('dept')->toArray();
            $data = $rows->pluck('total')->map(fn ($v) => (int) $v)->toArray();

            return ['labels' => $labels, 'data' => $data];
        });

        // Attendance statistics for current month (present/absent/late counts)
        $attendanceStats = Cache::remember('dashboard_admin_attendance_stats_month', 300, function () {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();

            $rows = DB::table('attendances')
                ->selectRaw('status, COUNT(*) as total')
                ->whereBetween('date', [$start, $end])
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'present' => (int) ($rows['present'] ?? 0),
                'absent' => (int) ($rows['absent'] ?? 0),
                'late' => (int) ($rows['late'] ?? 0),
            ];
        });

        return view('dashboard.admin', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'totalDepartments',
            'totalSubjects',
            'totalUsers',
            'recentFees',
            'totalFeesCollected',
            'pendingFees',
            'upcomingExams',
            'recentNotices',
            'feeCollectionByMonth',
            'departmentDistribution',
            'attendanceStats'
        ));
    }

    /**
     * Display the teacher dashboard with role-specific analytics.
     */
    private function teacherDashboard()
    {
        $user = Auth::user();

        // Resolve teacher record
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            return $this->handleMissingRoleRecord($user, 'teacher');
        }

        // Subjects taught by this teacher (assumes subjects table has teacher_id)
        $subjects = Subject::with('class')
            ->where('teacher_id', $teacher->id)
            ->get();

        // Classes taught
        $classIds = $subjects->pluck('class_id')->filter()->unique()->values()->all();

        // Map of student counts per class (for table badges and chart)
        $studentCountByClass = [];
        if (!empty($classIds)) {
            $studentCountByClass = Student::whereIn('class_id', $classIds)
                ->where(function ($q) {
                    // A lot of codebases track an is_active flag; if not present, ignore filter gracefully
                    try {
                        $q->where('is_active', true);
                    } catch (\Throwable $e) {
                        // ignore if column doesn't exist
                    }
                })
                ->selectRaw('class_id, COUNT(*) as total')
                ->groupBy('class_id')
                ->pluck('total', 'class_id')
                ->toArray();
        }

        // Subject-wise student count dataset (labels = subject names, data = count per subject's class)
        $subjectWiseStudentCount = [
            'labels' => $subjects->map(fn ($s) => $s->name ?? ('Subject #' . $s->id))->values()->all(),
            'data' => $subjects->map(function ($s) use ($studentCountByClass) {
                $cid = $s->class_id;
                return (int) ($studentCountByClass[$cid] ?? 0);
            })->values()->all(),
        ];

        // Recent attendance summary (last 7 days) across teacher's classes
        $recentAttendanceSummary = (function () use ($classIds) {
            $days = collect(range(6, 0))->map(fn ($i) => Carbon::now()->subDays($i)->startOfDay());
            if (empty($classIds)) {
                return [
                    'labels' => $days->map(fn ($d) => $d->format('d M'))->toArray(),
                    'data' => $days->map(fn () => 0)->toArray(),
                ];
            }

            $rows = DB::table('attendances')
                ->selectRaw('DATE(date) as d, SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present, COUNT(*) as total')
                ->whereIn('class_id', $classIds)
                ->whereBetween('date', [$days->first()->copy()->startOfDay(), $days->last()->copy()->endOfDay()])
                ->groupBy('d')
                ->pluck('present', 'd')
                ->toArray();

            $totals = DB::table('attendances')
                ->selectRaw('DATE(date) as d, COUNT(*) as total')
                ->whereIn('class_id', $classIds)
                ->whereBetween('date', [$days->first()->copy()->startOfDay(), $days->last()->copy()->endOfDay()])
                ->groupBy('d')
                ->pluck('total', 'd')
                ->toArray();

            $labels = $days->map(fn ($d) => $d->format('d M'))->toArray();
            $data = $days->map(function ($d) use ($rows, $totals) {
                $key = $d->format('Y-m-d');
                $present = (int) ($rows[$key] ?? 0);
                $total = (int) ($totals[$key] ?? 0);
                return $total > 0 ? round(($present / $total) * 100, 2) : 0;
            })->toArray();

            return ['labels' => $labels, 'data' => $data];
        })();

        // Class performance dataset for radar chart (latest exam across teacher's classes)
        $classPerformance = (function () use ($subjects, $classIds) {
            if (empty($classIds)) {
                return ['labels' => [], 'data' => []];
            }

            $latestExam = Exam::whereIn('class_id', $classIds)
                ->orderBy('start_date', 'desc')
                ->first();

            if (!$latestExam) {
                return ['labels' => [], 'data' => []];
            }

            // Average marks per subject (assumes marks out of 100)
            $subjectIds = $subjects->pluck('id')->all();
            if (empty($subjectIds)) {
                return ['labels' => [], 'data' => []];
            }

            $rows = DB::table('results')
                ->selectRaw('subject_id, AVG(marks_obtained) as avg_marks')
                ->where('exam_id', $latestExam->id)
                ->whereIn('subject_id', $subjectIds)
                ->groupBy('subject_id')
                ->pluck('avg_marks', 'subject_id')
                ->toArray();

            $labels = [];
            $data = [];
            foreach ($subjects as $s) {
                $labels[] = $s->name ?? ('Subject #' . $s->id);
                $data[] = (float) ($rows[$s->id] ?? 0);
            }

            return ['labels' => $labels, 'data' => $data];
        })();

        // Upcoming exams for teacher's classes
        $upcomingExams = Exam::with('class')
            ->whereIn('class_id', $classIds ?: [-1])
            ->whereDate('start_date', '>=', Carbon::now())
            ->orderBy('start_date', 'asc')
            ->take(6)
            ->get();

        // Recent notices for teachers (and all)
        $recentNotices = Notice::with('publishedBy')
            ->where('is_active', 1)
            ->where('publish_date', '<=', Carbon::now())
            ->where(function ($q) {
                $q->where('notice_for', 'teachers')->orWhere('notice_for', 'all');
            })
            ->where(function ($e) {
                $e->whereNull('expiry_date')->orWhere('expiry_date', '>=', Carbon::now());
            })
            ->latest()
            ->take(6)
            ->get();

        // Additional datasets or lists for the teacher view may exist; we pass the essentials used by charts/tables.
        return view('dashboard.teacher', compact(
            'teacher',
            'subjects',
            'studentCountByClass',
            'subjectWiseStudentCount',
            'recentAttendanceSummary',
            'classPerformance',
            'upcomingExams',
            'recentNotices'
        ));
    }

    /**
     * Display the student dashboard with analytics and trends.
     */
    private function studentDashboard()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return $this->handleMissingRoleRecord($user, 'student');
        }

        // Attendance stats (overall)
        $attendanceCount = Attendance::where('student_id', $student->id)->count();
        $presentCount = Attendance::where('student_id', $student->id)->where('status', 'present')->count();
        $attendancePercentage = $attendanceCount > 0 ? round(($presentCount / $attendanceCount) * 100, 2) : 0;

        // Upcoming exams relevant to student's class
        $upcomingExams = Exam::where('class_id', $student->class_id)
            ->whereDate('start_date', '>=', Carbon::now())
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        // Recent results
        $recentResults = Result::with(['exam', 'subject'])
            ->where('student_id', $student->id)
            ->latest()
            ->take(5)
            ->get();

        // Pending fees items list for table (unpaid or partial)
        $pendingFeeItems = Fee::where('student_id', $student->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Pending fees (sum outstanding)
        $pendingFees = (float) DB::table('fees')
            ->selectRaw('COALESCE(SUM(CASE WHEN status = "unpaid" THEN amount WHEN status = "partial" THEN amount - paid_amount ELSE 0 END),0) as due')
            ->where('student_id', $student->id)
            ->value('due');

        // Recent notices for students
        $recentNotices = Notice::where('is_active', 1)
            ->where('publish_date', '<=', Carbon::now())
            ->where(function ($q) {
                $q->where('notice_for', 'students')
                  ->orWhere('notice_for', 'all');
            })
            ->where(function ($e) {
                $e->whereNull('expiry_date')->orWhere('expiry_date', '>=', Carbon::now());
            })
            ->latest()
            ->take(5)
            ->get();

        // Attendance trend (last 30 days) for this student
        $attendanceTrend = (function () use ($student) {
            $days = collect(range(29, 0))->map(fn ($i) => Carbon::now()->subDays($i)->startOfDay());
            $records = Attendance::where('student_id', $student->id)
                ->whereBetween('date', [$days->first()->copy()->startOfDay(), $days->last()->copy()->endOfDay()])
                ->selectRaw('DATE(date) as d, MAX(status) as status') // if multiple in a day, any present wins later logic
                ->groupBy('d')
                ->pluck('status', 'd')
                ->toArray();

            $labels = $days->map(fn ($d) => $d->format('d M'))->toArray();
            $data = $days->map(function ($d) use ($records) {
                $key = $d->format('Y-m-d');
                $status = $records[$key] ?? null;
                // 100 if present, 50 if late, 0 if absent or none
                if ($status === 'present') return 100;
                if ($status === 'late') return 50;
                return 0;
            })->toArray();

            return ['labels' => $labels, 'data' => $data];
        })();

        // Subject-wise performance from latest exam for this student (radar)
        $subjectWisePerformance = (function () use ($student) {
            $latestExam = Exam::where('class_id', $student->class_id)
                ->orderBy('start_date', 'desc')
                ->first();

            if (!$latestExam) {
                return ['labels' => [], 'data' => []];
            }

            $rows = Result::with('subject')
                ->where('student_id', $student->id)
                ->where('exam_id', $latestExam->id)
                ->get();

            $labels = $rows->map(fn ($r) => optional($r->subject)->name ?? ('Subject #' . $r->subject_id))->toArray();
            // Assume marks out of 100
            $data = $rows->map(fn ($r) => (float) ($r->marks_obtained ?? 0))->toArray();

            return ['labels' => $labels, 'data' => $data];
        })();

        // GPA trend over last 3 exams (approximate: average percentage / 20 to map to 0-5 scale)
        $gpaTrend = (function () use ($student) {
            $exams = Exam::where('class_id', $student->class_id)
                ->orderBy('start_date', 'desc')
                ->take(3)
                ->get()
                ->reverse()
                ->values();

            if ($exams->isEmpty()) {
                return ['labels' => [], 'data' => []];
            }

            $labels = [];
            $data = [];
            foreach ($exams as $exam) {
                $labels[] = $exam->name ?? $exam->start_date?->format('d M Y') ?? ('Exam #' . $exam->id);
                $results = Result::where('student_id', $student->id)
                    ->where('exam_id', $exam->id)
                    ->get();
                if ($results->count() === 0) {
                    $data[] = 0;
                    continue;
                }
                $avg = (float) $results->avg('marks_obtained'); // assume out of 100
                $gpa = round($avg / 20, 2); // 0..5 scale
                $data[] = $gpa;
            }

            return ['labels' => $labels, 'data' => $data];
        })();

        // Fee payment timeline: label by due_date, datasets for Due and Paid amounts
        $feePaymentTimeline = (function () use ($student) {
            $fees = Fee::where('student_id', $student->id)
                ->orderBy('due_date', 'asc')
                ->get();

            $labels = $fees->map(function ($f) {
                if (!empty($f->due_date)) {
                    try {
                        return Carbon::parse($f->due_date)->format('d M');
                    } catch (\Throwable $e) {
                        return (string) $f->due_date;
                    }
                }
                return 'Invoice #' . $f->id;
            })->toArray();

            $paid = $fees->map(function ($f) {
                return (float) ($f->paid_amount ?? 0);
            })->toArray();

            $due = $fees->map(function ($f) {
                $amount = (float) ($f->amount ?? 0);
                $paidAmt = (float) ($f->paid_amount ?? 0);
                return max(0, $amount - $paidAmt);
            })->toArray();

            // Keep 'data' as paid amounts to align with chart usage in the view
            return ['labels' => $labels, 'data' => $paid, 'due' => $due];
        })();

        // Monthly attendance percentage for last 6 months
        $monthlyAttendancePercent = (function () use ($student) {
            $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());
            $labels = $months->map(fn ($m) => $m->format('F'))->toArray();
            $data = $months->map(function ($m) use ($student) {
                $start = $m->copy()->startOfMonth();
                $end = $m->copy()->endOfMonth();
                $total = Attendance::where('student_id', $student->id)->whereBetween('date', [$start, $end])->count();
                $present = Attendance::where('student_id', $student->id)->whereBetween('date', [$start, $end])->where('status', 'present')->count();
                return $total > 0 ? round(($present / $total) * 100, 2) : 0;
            })->toArray();

            return ['labels' => $labels, 'data' => $data];
        })();

        // Best and worst subject (from latest exam results)
        $bestSubject = null;
        $worstSubject = null;
        if (!empty($subjectWisePerformance['labels'])) {
            $maxIdx = 0;
            $minIdx = 0;
            $maxVal = -INF;
            $minVal = INF;
            foreach ($subjectWisePerformance['data'] as $i => $val) {
                if ($val > $maxVal) { $maxVal = $val; $maxIdx = $i; }
                if ($val < $minVal) { $minVal = $val; $minIdx = $i; }
            }
            $bestSubject = $subjectWisePerformance['labels'][$maxIdx] ?? null;
            $worstSubject = $subjectWisePerformance['labels'][$minIdx] ?? null;
        }

        return view('dashboard.student', compact(
            'student',
            'attendanceCount',
            'presentCount',
            'attendancePercentage',
            'upcomingExams',
            'recentResults',
            'pendingFees',
            'pendingFeeItems',
            'recentNotices',
            'attendanceTrend',
            'subjectWisePerformance',
            'gpaTrend',
            'feePaymentTimeline',
            'monthlyAttendancePercent',
            'bestSubject',
            'worstSubject'
        ));
    }

    /**
     * Display the staff dashboard (fallback).
     */
    private function staffDashboard()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = ClassRoom::count();

        $recentFees = Fee::with('student.user')
            ->latest()
            ->take(5)
            ->get();

        $recentNotices = Notice::where('is_active', 1)
            ->where('publish_date', '<=', Carbon::now())
            ->where(function ($q) {
                $q->where('notice_for', 'staff')
                    ->orWhere('notice_for', 'all');
            })
            ->where(function ($e) {
                $e->whereNull('expiry_date')->orWhere('expiry_date', '>=', Carbon::now());
            })
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.staff', compact(
            'totalStudents',
            'totalTeachers',
            'totalClasses',
            'recentFees',
            'recentNotices'
        ));
    }

    /**
     * Guardian dashboard.
     */
    private function guardianDashboard()
    {
        $user = Auth::user();
        $guardian = Guardian::with(['user', 'students.user', 'students.class'])->where('user_id', $user->id)->first();

        if (!$guardian) {
            return $this->handleMissingRoleRecord($user, 'guardian');
        }

        $students = $guardian->students()->with(['user', 'class', 'attendances', 'fees', 'results'])->get();
        $totalChildren = $students->count();

        // Total pending across all children where guardian has financial responsibility
        $totalPendingFees = 0.0;
        foreach ($students as $student) {
            $linked = $guardian->students()->where('students.id', $student->id)->first();
            $financial = $linked ? (bool)$linked->pivot->financial_responsibility : false;
            if ($financial) {
                $totalPendingFees += (float) $student->getPendingFeesAmount();
            }
        }

        // Average attendance across children
        $attendanceSum = 0.0;
        foreach ($students as $s) {
            $attendanceSum += (float) $s->getAttendanceRate();
        }
        $averageAttendance = $totalChildren > 0 ? round($attendanceSum / $totalChildren, 2) : 0.0;

        // Upcoming exams for all children's classes
        $classIds = $students->pluck('class_id')->filter()->unique()->values()->all();
        $upcomingExams = Exam::with('class')
            ->whereIn('class_id', $classIds ?: [-1])
            ->whereDate('start_date', '>=', Carbon::now())
            ->orderBy('start_date')
            ->take(10)
            ->get();

        // Recent notices for guardians or all
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

        // Simple chart placeholders
        $attendanceTrend = [
            'labels' => $students->map(fn ($s) => $s->user->name)->values()->all(),
            'data' => $students->map(fn ($s) => (float) $s->getAttendanceRate())->values()->all(),
        ];

        $feePaymentStatus = [
            'labels' => $students->map(fn ($s) => $s->user->name)->values()->all(),
            'data' => $students->map(function ($s) {
                $total = (float) $s->fees()->sum('amount');
                $paid = (float) $s->fees()->sum('paid_amount');
                return $total > 0 ? round(($paid / $total) * 100, 2) : 0;
            })->values()->all(),
        ];

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
     * Accountant dashboard.
     */
    private function accountantDashboard()
    {
        $user = Auth::user();
        $accountant = Accountant::with('user')->where('user_id', $user->id)->first();
        if (!$accountant) {
            return $this->handleMissingRoleRecord($user, 'accountant');
        }

        // Financial statistics
        $today = Carbon::now()->toDateString();
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd = Carbon::now()->endOfMonth()->toDateString();

        $todayCollection = (float) Fee::whereDate('payment_date', $today)->sum('paid_amount');
        $monthCollection = (float) Fee::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('paid_amount');
        $totalPending = (float) DB::table('fees')
            ->selectRaw('COALESCE(SUM(CASE WHEN status = "unpaid" THEN amount WHEN status = "partial" THEN amount - paid_amount ELSE 0 END),0) as due')
            ->value('due');
        $totalOverdue = (float) DB::table('fees')
            ->selectRaw('COALESCE(SUM(CASE WHEN due_date < NOW() AND status != "paid" THEN amount - COALESCE(paid_amount,0) ELSE 0 END),0) as due')
            ->value('due');

        // Pending waivers count (if they have approval permission)
        $pendingWaivers = DB::table('fee_waivers')->where('status', 'pending')->count();

        // Recent transactions
        $recentTransactions = Fee::with(['student.user', 'collectedBy'])
            ->whereNotNull('payment_date')
            ->latest('payment_date')
            ->take(10)
            ->get();

        // Simple trends: last 6 months collection
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());
        $collectionTrend = (function () use ($months) {
            $sums = DB::table('fees')
                ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m-01") as month_start, SUM(paid_amount) as total')
                ->where('status', 'paid')
                ->whereBetween('payment_date', [$months->first()->copy()->startOfMonth(), $months->last()->copy()->endOfMonth()])
                ->groupBy('month_start')
                ->pluck('total', 'month_start');

            $labels = $months->map(fn ($m) => $m->format('F'))->toArray();
            $data = $months->map(function ($m) use ($sums) {
                $key = $m->format('Y-m-01');
                return (float) ($sums[$key] ?? 0);
            })->toArray();

            return ['labels' => $labels, 'data' => $data];
        })();

        // Fee type breakdown (paid amounts by type)
        $feeTypeBreakdownRows = DB::table('fees')
            ->selectRaw('fee_type, SUM(paid_amount) as total')
            ->whereIn('status', ['paid', 'partial'])
            ->groupBy('fee_type')
            ->get();
        $feeTypeBreakdown = [
            'labels' => $feeTypeBreakdownRows->pluck('fee_type')->toArray(),
            'data' => $feeTypeBreakdownRows->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
        ];

        // Payment method distribution
        $paymentMethodRows = DB::table('fees')
            ->selectRaw('payment_method, SUM(paid_amount) as total')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get();
        $paymentMethodDistribution = [
            'labels' => $paymentMethodRows->pluck('payment_method')->toArray(),
            'data' => $paymentMethodRows->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
        ];

        // Waiver statistics
        $waiverStatsRows = DB::table('fee_waivers')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();
        $waiverStatistics = [
            'labels' => $waiverStatsRows->pluck('status')->toArray(),
            'data' => $waiverStatsRows->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
        ];

        return view('dashboard.accountant', compact(
            'accountant',
            'todayCollection',
            'monthCollection',
            'totalPending',
            'totalOverdue',
            'pendingWaivers',
            'recentTransactions',
            'collectionTrend',
            'feeTypeBreakdown',
            'paymentMethodDistribution',
            'waiverStatistics'
        ));
    }

    /**
     * Helper to standardize chart data structure for Chart.js
     */
    private function formatChartData($data, $labelKey, $valueKey)
    {
        $labels = [];
        $values = [];

        foreach ($data as $row) {
            if (is_array($row)) {
                $labels[] = $row[$labelKey] ?? '';
                $values[] = (float) ($row[$valueKey] ?? 0);
            } elseif (is_object($row)) {
                $labels[] = $row->{$labelKey} ?? '';
                $values[] = (float) ($row->{$valueKey} ?? 0);
            }
        }

        return ['labels' => $labels, 'data' => $values];
    }

    /**
     * Handle missing role record with logging and user-friendly error message.
     *
     * @param User $user
     * @param string $role
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handleMissingRoleRecord(User $user, string $role)
    {
        // Log the missing role record for admin investigation
        Log::error(ucfirst($role) . ' record missing for user', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'timestamp' => now()
        ]);
        
        // Redirect to profile with actionable error message
        return redirect()->route('profile.show')->with('error', 
            'Your ' . $role . ' profile is incomplete. Please contact the administrator at ' . 
            config('app.admin_email', 'admin@darulabrar.edu') . 
            ' to complete your profile setup.'
        );
    }

    /**
     * Display System Health Dashboard for admins.
     */
    public function systemHealth(Request $request)
    {
        try {
            // Check if cache refresh is requested
            $refresh = $request->query('refresh') === '1';
            
            $healthData = $this->getSystemHealthData($refresh);
            
            return view('dashboard.system-health', $healthData);
        } catch (\Throwable $e) {
            Log::error('Failed to load system health dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard')->with('error', 
                'Failed to load system health dashboard. Please try again or contact support.'
            );
        }
    }

    /**
     * Export system health report as PDF.
     */
    public function exportSystemHealth()
    {
        try {
            $healthData = $this->getSystemHealthData();
            $healthData['generatedAt'] = now()->format('Y-m-d H:i:s');
            $healthData['generatedBy'] = Auth::user()->name;
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.system-health-pdf', $healthData);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'system-health-report-' . now()->format('Y-m-d') . '.pdf';
            
            Log::info('System health report exported', [
                'user_id' => Auth::id(),
                'filename' => $filename
            ]);
            
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            Log::error('Failed to export system health report', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 
                'Failed to export health report. Please try again.'
            );
        }
    }

    /**
     * Run verification command.
     */
    public function runVerification(Request $request)
    {
        try {
            $request->validate([
                'role' => 'nullable|in:teacher,student,guardian,accountant'
            ]);
            
            $options = [];
            if ($request->role) {
                $options['--role'] = $request->role;
            }
            
            \Illuminate\Support\Facades\Artisan::call('verify:role-records', $options);
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            Log::info('Verification command executed', [
                'user_id' => Auth::id(),
                'role' => $request->role
            ]);
            
            return redirect()->back()->with('success', 'Verification completed successfully.')
                ->with('command_output', $output);
        } catch (\Throwable $e) {
            Log::error('Failed to run verification', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 
                'Failed to run verification: ' . $e->getMessage()
            );
        }
    }

    /**
     * Run sync command.
     */
    public function runSync(Request $request)
    {
        try {
            $request->validate([
                'role' => 'nullable|in:admin,teacher,student,staff,guardian,accountant',
                'repair' => 'boolean'
            ]);
            
            $options = [];
            if ($request->role) {
                $options['--role'] = $request->role;
            }
            if ($request->repair) {
                $options['--repair'] = true;
                $options['--force'] = true;
            }
            
            \Illuminate\Support\Facades\Artisan::call('sync:spatie-roles', $options);
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            Log::info('Sync command executed', [
                'user_id' => Auth::id(),
                'role' => $request->role,
                'repair' => $request->repair
            ]);
            
            return redirect()->back()->with('success', 'Sync completed successfully.')
                ->with('command_output', $output);
        } catch (\Throwable $e) {
            Log::error('Failed to run sync', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 
                'Failed to run sync: ' . $e->getMessage()
            );
        }
    }

    /**
     * Run repair command.
     */
    public function runRepair(Request $request)
    {
        try {
            $request->validate([
                'role' => 'nullable|in:teacher,student,guardian,accountant'
            ]);
            
            $options = ['--repair' => true, '--force' => true];
            if ($request->role) {
                $options['--role'] = $request->role;
            }
            
            \Illuminate\Support\Facades\Artisan::call('verify:role-records', $options);
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            Log::info('Repair command executed', [
                'user_id' => Auth::id(),
                'role' => $request->role
            ]);
            
            return redirect()->back()->with('success', 'Repair completed successfully.')
                ->with('command_output', $output);
        } catch (\Throwable $e) {
            Log::error('Failed to run repair', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return redirect()->back()->with('error', 
                'Failed to run repair: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get system health data (reusable for both view and export).
     */
    private function getSystemHealthData(bool $refresh = false): array
    {
        $cacheKey = 'system_health_data';
        
        // Use cache unless refresh is requested
        if (!$refresh) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }
        
        $data = Cache::remember($cacheKey, 300, function () {
            // 1. Data Integrity Checks - Fully optimized with SQL queries
            $missingRoleRecords = [];
            $roles = ['teacher', 'student', 'guardian', 'accountant'];
            $roleTableMap = [
                'teacher' => 'teachers',
                'student' => 'students',
                'guardian' => 'guardians',
                'accountant' => 'accountants'
            ];
            
            $totalMissing = 0;
            foreach ($roles as $role) {
                $table = $roleTableMap[$role];
                
                // Get missing records using left join (SQL-only, no full user load)
                $missingUsers = DB::table('users as u')
                    ->leftJoin("{$table} as t", 't.user_id', '=', 'u.id')
                    ->where('u.role', $role)
                    ->whereNull('t.id')
                    ->select('u.id', 'u.name', 'u.email', 'u.created_at', 'u.role')
                    ->limit(200)
                    ->get();
                
                // Convert to User models for view compatibility
                $missingRoleRecords[$role] = User::hydrate($missingUsers->toArray());
                
                // Get total count without loading rows
                $totalMissing += DB::table('users as u')
                    ->leftJoin("{$table} as t", 't.user_id', '=', 'u.id')
                    ->where('u.role', $role)
                    ->whereNull('t.id')
                    ->count();
            }
            
            // Orphaned records - Using whereNotIn for compatibility
            $userIds = User::pluck('id');
            
            $orphanedRecords = [
                'teacher' => Teacher::whereNotIn('user_id', $userIds)->limit(200)->get(),
                'student' => Student::whereNotIn('user_id', $userIds)->limit(200)->get(),
                'guardian' => Guardian::whereNotIn('user_id', $userIds)->limit(200)->get(),
                'accountant' => Accountant::whereNotIn('user_id', $userIds)->limit(200)->get(),
            ];
            
            $totalOrphaned = Teacher::whereNotIn('user_id', $userIds)->count()
                + Student::whereNotIn('user_id', $userIds)->count()
                + Guardian::whereNotIn('user_id', $userIds)->count()
                + Accountant::whereNotIn('user_id', $userIds)->count();
            
            // 2. Spatie Role Sync Status - Optimized with targeted queries
            
            // Missing Spatie roles (no roles assigned, excluding admin/staff)
            $missingSpatieRoles = User::doesntHave('roles')
                ->whereNotIn('role', ['admin', 'staff'])
                ->select('id', 'name', 'email', 'role')
                ->limit(200)
                ->get();
            
            // Mismatched Spatie roles (one role but wrong one)
            $mismatchedUserIds = DB::table('users as u')
                ->join('model_has_roles as mhr', 'mhr.model_id', '=', 'u.id')
                ->join('roles as r', 'r.id', '=', 'mhr.role_id')
                ->where('mhr.model_type', 'App\\Models\\User')
                ->whereColumn('r.name', '!=', 'u.role')
                ->groupBy('u.id')
                ->havingRaw('COUNT(DISTINCT r.id) = 1')
                ->pluck('u.id')
                ->take(200);
            
            $mismatchedSpatieRoles = User::with('roles:id,name')
                ->whereIn('id', $mismatchedUserIds)
                ->select('id', 'name', 'email', 'role')
                ->get();
            
            // Multiple Spatie roles
            $multipleSpatieRoles = User::has('roles', '>', 1)
                ->with('roles:id,name')
                ->select('id', 'name', 'email', 'role')
                ->limit(200)
                ->get();
            
            // Migration progress
            $totalUsers = User::count();
            $usersWithRoles = User::has('roles')->count();
            $migrationProgress = $totalUsers > 0 ? round(($usersWithRoles / $totalUsers) * 100, 1) : 0;
            
            // 3. Database Statistics
            $usersByRole = [];
            foreach (['admin', 'teacher', 'student', 'staff', 'guardian', 'accountant'] as $role) {
                $usersByRole[$role] = User::where('role', $role)->count();
            }
            
            $activeInactiveStats = [];
            foreach (['admin', 'teacher', 'student', 'staff', 'guardian', 'accountant'] as $role) {
                $activeInactiveStats[$role] = [
                    'active' => User::where('role', $role)->where('is_active', true)->count(),
                    'inactive' => User::where('role', $role)->where('is_active', false)->count(),
                ];
            }
            
            $roleRecordCounts = [
                'teachers' => Teacher::count(),
                'students' => Student::count(),
                'guardians' => Guardian::count(),
                'accountants' => Accountant::count(),
            ];
            
            // 4. Calculate Health Score
            $totalIssues = $totalMissing + $totalOrphaned + $missingSpatieRoles->count() + $mismatchedSpatieRoles->count() + $multipleSpatieRoles->count();
            $healthPercentage = max(0, 100 - (($totalIssues / max($totalUsers, 1)) * 100));
            
            if ($healthPercentage >= 100) {
                $healthStatus = 'excellent';
                $healthColor = 'green';
            } elseif ($healthPercentage >= 95) {
                $healthStatus = 'good';
                $healthColor = 'green';
            } elseif ($healthPercentage >= 85) {
                $healthStatus = 'warning';
                $healthColor = 'yellow';
            } else {
                $healthStatus = 'critical';
                $healthColor = 'red';
            }
            
            $healthScore = round($healthPercentage, 1);
            
            // 5. Recent Activity Logs
            $recentLogs = DB::table('activity_logs')
                ->whereIn('log_name', ['system', 'roles', 'users'])
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
            
            return compact(
                'missingRoleRecords',
                'orphanedRecords',
                'missingSpatieRoles',
                'mismatchedSpatieRoles',
                'multipleSpatieRoles',
                'usersByRole',
                'activeInactiveStats',
                'roleRecordCounts',
                'healthScore',
                'healthStatus',
                'healthColor',
                'migrationProgress',
                'recentLogs',
                'totalIssues',
                'totalMissing',
                'totalOrphaned'
            );
        });
        
        return $data;
    }
}
