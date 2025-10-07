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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            // Fallback to staff dashboard if mapping missing
            return $this->staffDashboard();
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
            return $this->staffDashboard();
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
}
