<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\FeeWaiver;
use App\Models\FeeInstallment;
use App\Repositories\FeeRepository;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Carbon\Carbon;

class AccountantPortalController extends Controller
{
    protected FeeRepository $fees;
    protected ActivityLogService $activity;

    public function __construct(FeeRepository $fees, ActivityLogService $activity)
    {
        $this->middleware(['auth', 'role:accountant']);
        $this->fees = $fees;
        $this->activity = $activity;
    }

    /**
     * Accountant dashboard: reuse the dashboard.accountant view prepared by DashboardController.
     */
    public function dashboard()
    {
        // Compute minimal stats (DashboardController already has a comprehensive version)
        $today = Carbon::now()->toDateString();
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthEnd = Carbon::now()->endOfMonth()->toDateString();

        $todayCollection = (float) Fee::whereDate('payment_date', $today)->sum('paid_amount');
        $monthCollection = (float) Fee::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('paid_amount');

        // Portable calculations across SQLite/MySQL/PostgreSQL
        $totalUnpaid = (float) DB::table('fees')->where('status', 'unpaid')->sum('amount');
        $totalPartial = (float) DB::table('fees')->where('status', 'partial')->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));
        $totalPending = $totalUnpaid + $totalPartial;

        $totalOverdue = (float) DB::table('fees')
            ->where('status', '!=', 'paid')
            ->whereDate('due_date', '<', $today)
            ->sum(DB::raw('amount - COALESCE(paid_amount, 0)'));

        $pendingWaivers = DB::table('fee_waivers')->where('status', 'pending')->count();

        $recentTransactions = Fee::with(['student.user', 'collectedBy'])
            ->whereNotNull('payment_date')
            ->latest('payment_date')
            ->take(10)
            ->get();

        // Basic charts placeholders
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i)->startOfMonth());
        $collectionTrend = (function () use ($months) {
            $labels = $months->map(fn ($m) => $m->format('F'))->toArray();
            $data = $months->map(function ($m) {
                $start = $m->copy()->startOfMonth()->toDateString();
                $end = $m->copy()->endOfMonth()->toDateString();
                return (float) DB::table('fees')
                    ->where('status', 'paid')
                    ->whereBetween('payment_date', [$start, $end])
                    ->sum('paid_amount');
            })->toArray();

            return ['labels' => $labels, 'data' => $data];
        })();

        $feeTypeBreakdownRows = DB::table('fees')
            ->selectRaw('fee_type, SUM(paid_amount) as total')
            ->whereIn('status', ['paid', 'partial'])
            ->groupBy('fee_type')
            ->get();
        $feeTypeBreakdown = [
            'labels' => $feeTypeBreakdownRows->pluck('fee_type')->toArray(),
            'data' => $feeTypeBreakdownRows->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
        ];

        $paymentMethodRows = DB::table('fees')
            ->selectRaw('payment_method, SUM(paid_amount) as total')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get();
        $paymentMethodDistribution = [
            'labels' => $paymentMethodRows->pluck('payment_method')->toArray(),
            'data' => $paymentMethodRows->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
        ];

        $waiverStatsRows = DB::table('fee_waivers')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();
        $waiverStatistics = [
            'labels' => $waiverStatsRows->pluck('status')->toArray(),
            'data' => $waiverStatsRows->pluck('total')->map(fn ($v) => (int) $v)->toArray(),
        ];

        // Reuse dashboard.accountant view template
        return view('dashboard.accountant', compact(
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
     * Fees management listing.
     */
    public function fees(Request $request)
    {
        // Minimal listing for now; full filters can be added later
        $query = Fee::with(['student.user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('fee_type'), fn ($q) => $q->where('fee_type', $request->fee_type))
            ->orderBy('due_date', 'asc');

        $fees = $query->paginate(20);

        // If no dedicated view exists yet, redirect with info to dashboard
        if (!view()->exists('accountant.fees')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Fees list is coming soon.');
        }

        return view('accountant.fees', compact('fees'));
    }

    /**
     * Show payment form (placeholder).
     */
    public function recordPayment(Fee $fee)
    {
        if (!view()->exists('accountant.record-payment')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Record payment form is coming soon.');
        }
        return view('accountant.record-payment', compact('fee'));
    }

    /**
     * Process payment via repository.
     */
    public function processPayment(Request $request, Fee $fee)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:500',
        ]);

        $this->fees->recordPayment($fee, $data);

        return redirect()->route('accountant.fees')->with('success', 'Payment recorded successfully.');
    }

    /**
     * Waivers listing (placeholder).
     */
    public function waivers(Request $request)
    {
        $waivers = FeeWaiver::with(['student.user', 'fee'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        if (!view()->exists('accountant.waivers')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Waivers module is coming soon.');
        }

        return view('accountant.waivers', compact('waivers'));
    }

    public function createWaiver()
    {
        if (!view()->exists('accountant.create-waiver')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Create waiver form is coming soon.');
        }
        return view('accountant.create-waiver');
    }

    public function storeWaiver(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'waiver_type' => 'required|string|max:50',
            'amount_type' => 'required|in:percentage,fixed',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ]);

        FeeWaiver::create(array_merge($data, [
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]));

        return redirect()->route('accountant.waivers')->with('success', 'Waiver created (pending approval).');
    }

    public function approveWaiver(FeeWaiver $waiver)
    {
        try {
            $this->authorize('approve', $waiver);

            $waiver->status = 'approved';
            $waiver->approved_by = Auth::id();
            $waiver->approved_at = now();
            $waiver->save();

            // If this waiver is tied to a specific fee, apply it
            if (!empty($waiver->fee_id)) {
                $this->fees->applyWaiverToFee((int) $waiver->fee_id, (int) $waiver->id);
            }

            // Log activity
            $this->activity->logWaiverApproval($waiver, Auth::user());

            return back()->with('success', 'Waiver approved.');
        } catch (AuthorizationException $e) {
            return back()->with('error', 'You are not authorized to approve this waiver.');
        }
    }

    public function rejectWaiver(Request $request, FeeWaiver $waiver)
    {
        $data = $request->validate([
            'reason' => 'required|string',
        ]);

        try {
            $this->authorize('reject', $waiver);

            $waiver->status = 'rejected';
            $waiver->rejection_reason = $data['reason'];
            $waiver->save();

            // Log activity
            $this->activity->logWaiverRejection($waiver, Auth::user(), $data['reason']);

            return back()->with('success', 'Waiver rejected.');
        } catch (AuthorizationException $e) {
            return back()->with('error', 'You are not authorized to reject this waiver.');
        }
    }

    /**
     * Installments listing (placeholder).
     */
    public function installments()
    {
        $installments = FeeInstallment::with(['fee.student.user'])->latest()->paginate(20);

        if (!view()->exists('accountant.installments')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Installments module is coming soon.');
        }

        return view('accountant.installments', compact('installments'));
    }

    public function createInstallmentPlan(Fee $fee)
    {
        if (!view()->exists('accountant.create-installment-plan')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Create installment plan form is coming soon.');
        }
        return view('accountant.create-installment-plan', compact('fee'));
    }

    public function storeInstallmentPlan(Request $request, Fee $fee)
    {
        $data = $request->validate([
            'number_of_installments' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'frequency' => 'nullable|in:weekly,biweekly,monthly',
        ]);

        $this->fees->createInstallmentPlan(
            $fee->id,
            (int) $data['number_of_installments'],
            $data['start_date'],
            $data['frequency'] ?? 'monthly'
        );

        return redirect()->route('accountant.installments')->with('success', 'Installment plan created.');
    }

    /**
     * Late fees (placeholder).
     */
    public function lateFees()
    {
        if (!view()->exists('accountant.late-fees')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Late fees module is coming soon.');
        }
        return view('accountant.late-fees');
    }

    public function applyLateFees(Request $request)
    {
        // In future: accept selected fee IDs, for now run batch
        $count = $this->fees->processOverdueFees();

        return back()->with('success', "Applied late fees to {$count} records.");
    }

    /**
     * Reports (placeholder).
     */
    public function reports()
    {
        if (!view()->exists('accountant.reports')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Reports module is coming soon.');
        }
        return view('accountant.reports');
    }

    public function collectionReport()
    {
        if (!view()->exists('accountant.reports.collection')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Collection report is coming soon.');
        }
        return view('accountant.reports.collection');
    }

    public function outstandingReport()
    {
        if (!view()->exists('accountant.reports.outstanding')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Outstanding report is coming soon.');
        }
        return view('accountant.reports.outstanding');
    }

    public function waiverReport()
    {
        if (!view()->exists('accountant.reports.waivers')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Waiver report is coming soon.');
        }
        return view('accountant.reports.waivers');
    }

    /**
     * Reconciliation (placeholder).
     */
    public function reconciliation()
    {
        if (!view()->exists('accountant.reconciliation')) {
            return redirect()->route('accountant.dashboard')->with('info', 'Reconciliation module is coming soon.');
        }
        return view('accountant.reconciliation');
    }
}
