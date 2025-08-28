<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Fee::with(['student', 'collectedBy']);

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('fee_type') && $request->fee_type != '') {
            $query->where('fee_type', $request->fee_type);
        }

        if ($request->has('student_id') && $request->student_id != '') {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('due_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('due_date', '<=', $request->date_to);
        }

        // Get fee types for filter dropdown
        $feeTypes = Fee::select('fee_type')->distinct()->pluck('fee_type');
        
        // Get students for filter dropdown
        $students = Student::select('id', 'name', 'student_id')->orderBy('name')->get();

        $fees = $query->latest()->paginate(15);

        return view('fees.index', compact('fees', 'feeTypes', 'students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::select('id', 'name', 'student_id')->orderBy('name')->get();
        $feeTypes = ['admission', 'monthly', 'exam', 'library', 'transport', 'hostel', 'other'];
        
        return view('fees.create', compact('students', 'feeTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:paid,unpaid,partial',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        // If status is paid, ensure paid_amount equals amount
        if ($request->status == 'paid' && $request->paid_amount != $request->amount) {
            $request->merge(['paid_amount' => $request->amount]);
        }

        // If status is partial, ensure paid_amount is less than amount
        if ($request->status == 'partial' && $request->paid_amount >= $request->amount) {
            return back()->withInput()->withErrors(['paid_amount' => 'Paid amount must be less than total amount for partial payment']);
        }

        // If status is unpaid, set paid_amount to 0
        if ($request->status == 'unpaid') {
            $request->merge(['paid_amount' => 0]);
        }

        // Set payment date if paid or partial
        if (in_array($request->status, ['paid', 'partial']) && $request->paid_amount > 0) {
            $request->merge(['payment_date' => now()]);
            $request->merge(['collected_by' => Auth::id()]);
        }

        Fee::create($request->all());

        return redirect()->route('fees.index')->with('success', 'Fee created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $fee = Fee::with(['student', 'collectedBy'])->findOrFail($id);
        return view('fees.show', compact('fee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $fee = Fee::findOrFail($id);
        $students = Student::select('id', 'name', 'student_id')->orderBy('name')->get();
        $feeTypes = ['admission', 'monthly', 'exam', 'library', 'transport', 'hostel', 'other'];
        
        return view('fees.edit', compact('fee', 'students', 'feeTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:paid,unpaid,partial',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'transaction_id' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $fee = Fee::findOrFail($id);

        // If status is paid, ensure paid_amount equals amount
        if ($request->status == 'paid' && $request->paid_amount != $request->amount) {
            $request->merge(['paid_amount' => $request->amount]);
        }

        // If status is partial, ensure paid_amount is less than amount
        if ($request->status == 'partial' && $request->paid_amount >= $request->amount) {
            return back()->withInput()->withErrors(['paid_amount' => 'Paid amount must be less than total amount for partial payment']);
        }

        // If status is unpaid, set paid_amount to 0
        if ($request->status == 'unpaid') {
            $request->merge(['paid_amount' => 0]);
        }

        // Set payment date if paid or partial and it wasn't set before
        if (in_array($request->status, ['paid', 'partial']) && $request->paid_amount > 0 && !$fee->payment_date) {
            $request->merge(['payment_date' => now()]);
            $request->merge(['collected_by' => Auth::id()]);
        }

        $fee->update($request->all());

        return redirect()->route('fees.index')->with('success', 'Fee updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $fee = Fee::findOrFail($id);
        $fee->delete();

        return redirect()->route('fees.index')->with('success', 'Fee deleted successfully');
    }

    /**
     * Display fees for the logged-in student.
     */
    public function myFees()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student record not found');
        }
        
        $fees = Fee::where('student_id', $student->id)
            ->latest()
            ->paginate(15);
            
        return view('fees.my-fees', compact('fees', 'student'));
    }

    /**
     * Generate invoice for a specific fee.
     */
    public function generateInvoice($id)
    {
        $fee = Fee::with(['student', 'collectedBy'])->findOrFail($id);
        
        $pdf = PDF::loadView('fees.invoice', compact('fee'));
        
        return $pdf->download('invoice-' . $fee->id . '.pdf');
    }

    /**
     * Record a payment for a fee.
     */
    public function recordPayment(Request $request, $id)
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $fee = Fee::findOrFail($id);
        
        // Calculate new paid amount
        $newPaidAmount = $fee->paid_amount + $request->paid_amount;
        
        // Determine new status
        $newStatus = 'partial';
        if ($newPaidAmount >= $fee->amount) {
            $newStatus = 'paid';
            $newPaidAmount = $fee->amount; // Ensure paid amount doesn't exceed total
        }
        
        // Update fee
        $fee->update([
            'paid_amount' => $newPaidAmount,
            'status' => $newStatus,
            'payment_date' => now(),
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'remarks' => $request->remarks,
            'collected_by' => Auth::id(),
        ]);
        
        return redirect()->route('fees.show', $fee->id)->with('success', 'Payment recorded successfully');
    }

    /**
     * Show fee collection report.
     */
    public function collectionReport(Request $request)
    {
        $query = Fee::with(['student', 'collectedBy'])
            ->whereIn('status', ['paid', 'partial'])
            ->where('paid_amount', '>', 0);
            
        // Apply filters
        if ($request->has('fee_type') && $request->fee_type != '') {
            $query->where('fee_type', $request->fee_type);
        }
        
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }
        
        // Get fee types for filter dropdown
        $feeTypes = Fee::select('fee_type')->distinct()->pluck('fee_type');
        
        // Get collection summary
        $summary = $query->select(
            DB::raw('SUM(paid_amount) as total_collected'),
            DB::raw('COUNT(*) as total_transactions'),
            'fee_type'
        )
        ->groupBy('fee_type')
        ->get();
        
        $totalCollected = $summary->sum('total_collected');
        
        // Get detailed collection data
        $collections = $query->latest('payment_date')->paginate(15);
        
        return view('fees.reports.collection', compact('collections', 'feeTypes', 'summary', 'totalCollected'));
    }

    /**
     * Show outstanding fees report.
     */
    public function outstandingReport(Request $request)
    {
        $query = Fee::with(['student'])
            ->whereIn('status', ['unpaid', 'partial']);
            
        // Apply filters
        if ($request->has('fee_type') && $request->fee_type != '') {
            $query->where('fee_type', $request->fee_type);
        }
        
        if ($request->has('overdue_only') && $request->overdue_only == '1') {
            $query->where('due_date', '<', now());
        }
        
        // Get fee types for filter dropdown
        $feeTypes = Fee::select('fee_type')->distinct()->pluck('fee_type');
        
        // Get outstanding summary
        $summary = $query->select(
            DB::raw('SUM(amount - paid_amount) as total_outstanding'),
            DB::raw('COUNT(*) as total_records'),
            'fee_type'
        )
        ->groupBy('fee_type')
        ->get();
        
        $totalOutstanding = $summary->sum('total_outstanding');
        
        // Get detailed outstanding data
        $outstandingFees = $query->latest('due_date')->paginate(15);
        
        return view('fees.reports.outstanding', compact('outstandingFees', 'feeTypes', 'summary', 'totalOutstanding'));
    }

    /**
     * Bulk fee creation form.
     */
    public function createBulk()
    {
        $students = Student::select('id', 'name', 'student_id', 'class_id')
            ->with('class')
            ->orderBy('class_id')
            ->orderBy('name')
            ->get();
            
        $feeTypes = ['admission', 'monthly', 'exam', 'library', 'transport', 'hostel', 'other'];
        
        return view('fees.create-bulk', compact('students', 'feeTypes'));
    }

    /**
     * Store bulk fees.
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'fee_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);
        
        $fees = [];
        foreach ($request->student_ids as $studentId) {
            $fees[] = [
                'student_id' => $studentId,
                'fee_type' => $request->fee_type,
                'amount' => $request->amount,
                'due_date' => $request->due_date,
                'status' => 'unpaid',
                'paid_amount' => 0,
                'remarks' => $request->remarks,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        Fee::insert($fees);
        
        return redirect()->route('fees.index')->with('success', count($fees) . ' fees created successfully');
    }
}