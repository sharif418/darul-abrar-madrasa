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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
}
