<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeeRequest;
use App\Http\Requests\UpdateFeeRequest;
use App\Models\ClassRoom;
use App\Models\Fee;
use App\Models\Student;
use App\Repositories\FeeRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeeController extends Controller
{
    protected $feeRepository;

    public function __construct(FeeRepository $feeRepository)
    {
        $this->feeRepository = $feeRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'status' => $request->status,
                'fee_type' => $request->fee_type,
                'student_id' => $request->student_id,
                'class_id' => $request->class_id,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ];

            $fees = $this->feeRepository->getAllWithFilters($filters, 15);
            $statistics = $this->feeRepository->getStatistics($filters);
            
            // Get data for filters
            $feeTypes = Fee::select('fee_type')->distinct()->pluck('fee_type', 'fee_type');
            $students = Student::with('user:id,name')->get();
            $classes = ClassRoom::with('department')->get();

            return view('fees.index', compact(
                'fees', 
                'feeTypes', 
                'students', 
                'classes',
                'statistics'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to load fees list', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load fees. Please try again.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::with('user:id,name')->get();
        $feeTypes = ['admission', 'monthly', 'exam', 'library', 'transport', 'hostel', 'other'];
        
        return view('fees.create', compact('students', 'feeTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeeRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Set payment date and collected_by if paid or partial
            if (in_array($data['status'], ['paid', 'partial']) && ($data['paid_amount'] ?? 0) > 0) {
                $data['payment_date'] = now();
                $data['collected_by'] = Auth::id();
            }

            $fee = $this->feeRepository->create($data);

            Log::info('Fee created successfully', [
                'fee_id' => $fee->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('fees.index')->with('success', 'Fee created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create fee', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to create fee. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $fee = Fee::with(['student.user', 'student.class', 'collectedBy'])->findOrFail($id);
            return view('fees.show', compact('fee'));
        } catch (\Exception $e) {
            Log::error('Failed to load fee details', [
                'fee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load fee details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $fee = Fee::findOrFail($id);
        $students = Student::with('user:id,name')->get();
        $feeTypes = ['admission', 'monthly', 'exam', 'library', 'transport', 'hostel', 'other'];
        
        return view('fees.edit', compact('fee', 'students', 'feeTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeeRequest $request, string $id)
    {
        try {
            $fee = Fee::findOrFail($id);
            $data = $request->validated();
            
            // Set payment date and collected_by if paid or partial and not set before
            if (in_array($data['status'], ['paid', 'partial']) && ($data['paid_amount'] ?? 0) > 0 && !$fee->payment_date) {
                $data['payment_date'] = now();
                $data['collected_by'] = Auth::id();
            }

            $this->feeRepository->update($fee, $data);

            Log::info('Fee updated successfully', [
                'fee_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('fees.index')->with('success', 'Fee updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update fee', [
                'fee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to update fee. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $fee = Fee::findOrFail($id);
            
            // Check if fee can be deleted (not paid)
            if ($fee->status === 'paid') {
                return back()->with('error', 'Cannot delete a paid fee. Please contact administrator.');
            }

            $fee->delete();

            Log::info('Fee deleted successfully', [
                'fee_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('fees.index')->with('success', 'Fee deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete fee', [
                'fee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to delete fee. Please try again.');
        }
    }

    /**
     * Display fees for the logged-in student.
     */
    public function myFees()
    {
        try {
            $student = Auth::user()->student;
            
            if (!$student) {
                return redirect()->route('dashboard')->with('error', 'Student record not found');
            }
            
            $fees = Fee::where('student_id', $student->id)
                ->latest()
                ->paginate(15);
                
            return view('fees.my-fees', compact('fees', 'student'));
        } catch (\Exception $e) {
            Log::error('Failed to load student fees', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load your fees. Please try again.');
        }
    }

    /**
     * Generate invoice for a specific fee.
     */
    public function generateInvoice($id)
    {
        try {
            $fee = Fee::with(['student.user', 'student.class', 'collectedBy'])->findOrFail($id);
            
            $pdf = Pdf::loadView('fees.invoice', compact('fee'));

            Log::info('Invoice generated', [
                'fee_id' => $id,
                'user_id' => Auth::id(),
            ]);
            
            return $pdf->download('invoice-' . $fee->id . '.pdf');
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice', [
                'fee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to generate invoice. Please try again.');
        }
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

        try {
            $fee = Fee::findOrFail($id);
            
            $paymentData = [
                'amount' => $request->paid_amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'remarks' => $request->remarks,
                'collected_by' => Auth::id(),
            ];

            $this->feeRepository->recordPayment($fee, $paymentData);

            Log::info('Payment recorded successfully', [
                'fee_id' => $id,
                'amount' => $request->paid_amount,
                'user_id' => Auth::id(),
            ]);
            
            return redirect()->route('fees.show', $fee->id)->with('success', 'Payment recorded successfully');
        } catch (\Exception $e) {
            Log::error('Failed to record payment', [
                'fee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->all(),
            ]);

            return back()->withInput()->with('error', 'Failed to record payment. Please try again.');
        }
    }

    /**
     * Show fee collection report.
     */
    public function collectionReport(Request $request)
    {
        try {
            $filters = [
                'fee_type' => $request->fee_type,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ];

            $result = $this->feeRepository->getCollectionReport($filters, 15);
            
            // Map repository result to view variables
            $collections = $result['fees'];
            $summary = $result['summary'];
            $totalCollected = $result['totalCollected'] ?? 0;

            // Get fee types for filter dropdown
            $feeTypes = Fee::select('fee_type')->distinct()->pluck('fee_type');
            
            return view('fees.reports.collection', compact('collections', 'summary', 'totalCollected', 'feeTypes'));
        } catch (\Exception $e) {
            Log::error('Failed to load collection report', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load collection report. Please try again.');
        }
    }

    /**
     * Show outstanding fees report.
     */
    public function outstandingReport(Request $request)
    {
        try {
            $filters = [
                'fee_type' => $request->fee_type,
                'overdue' => $request->boolean('overdue_only'),
            ];

            $result = $this->feeRepository->getOutstandingReport($filters, 15);

            // Map repository result to view variables
            $outstandingFees = $result['fees'];
            $summary = $result['summary'];
            $totalOutstanding = $result['totalOutstanding'] ?? 0;
            
            // Get fee types for filter dropdown
            $feeTypes = Fee::select('fee_type')->distinct()->pluck('fee_type');
            
            return view('fees.reports.outstanding', compact('outstandingFees', 'summary', 'totalOutstanding', 'feeTypes'));
        } catch (\Exception $e) {
            Log::error('Failed to load outstanding report', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load outstanding report. Please try again.');
        }
    }

    /**
     * Show payment form for a fee.
     */
    public function showPaymentForm($id)
    {
        try {
            $fee = Fee::with(['student.user'])->findOrFail($id);
            
            if ($fee->status === 'paid') {
                return redirect()->route('fees.show', $fee->id)
                    ->with('info', 'This fee has already been fully paid.');
            }
            
            return view('fees.payment', compact('fee'));
        } catch (\Exception $e) {
            Log::error('Failed to load payment form', [
                'fee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load payment form. Please try again.');
        }
    }

    /**
     * Show reports index page.
     */
    public function reportsIndex()
    {
        try {
            $statistics = $this->feeRepository->getStatistics([]);

            return view('fees.reports.index', compact('statistics'));
        } catch (\Exception $e) {
            Log::error('Failed to load reports index', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load reports. Please try again.');
        }
    }

    /**
     * Bulk fee creation form.
     */
    public function createBulk()
    {
        $students = Student::with(['user:id,name', 'class'])
            ->orderBy('class_id')
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
        
        try {
            $feeData = [
                'fee_type' => $request->fee_type,
                'amount' => $request->amount,
                'due_date' => $request->due_date,
                'remarks' => $request->remarks,
            ];

            $count = $this->feeRepository->createBulk($request->student_ids, $feeData);

            Log::info('Bulk fees created successfully', [
                'count' => $count,
                'user_id' => Auth::id(),
            ]);
            
            return redirect()->route('fees.index')->with('success', $count . ' fees created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create bulk fees', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->all(),
            ]);

            return back()->withInput()->with('error', 'Failed to create bulk fees. Please try again.');
        }
    }
}
