<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountantRequest;
use App\Http\Requests\UpdateAccountantRequest;
use App\Models\Accountant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountantController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
        $query = Accountant::with('user');

        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->boolean('active'));
        }
        if ($request->filled('q')) {
            $q = '%' . $request->input('q') . '%';
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', $q)
                    ->orWhere('email', 'like', $q);
            })->orWhere('employee_id', 'like', $q);
        }

        $accountants = $query->paginate(20);

        if (view()->exists('accountants.index')) {
            return view('accountants.index', compact('accountants'));
        }

        return response()->json([
            'message' => 'Accountants index view not implemented yet.',
            'total' => $accountants->total(),
        ]);
    }

    public function create()
    {
        if (view()->exists('accountants.create')) {
            return view('accountants.create');
        }

        return back()->with('info', 'Create accountant form coming soon.');
    }

    public function store(StoreAccountantRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            // Create linked user with accountant role
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'accountant',
                'phone' => $data['phone'],
                'is_active' => true,
            ]);

            // Assign spatie role if available
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('accountant');
                } catch (\Throwable $e) {
                    // ignore if roles not configured yet
                }
            }

            // Create accountant record
            Accountant::create([
                'user_id' => $user->id,
                'employee_id' => $data['employee_id'],
                'designation' => $data['designation'],
                'qualification' => $data['qualification'] ?? null,
                'phone' => $data['phone'],
                'address' => $data['address'],
                'joining_date' => $data['joining_date'],
                'salary' => $data['salary'],
                'can_approve_waivers' => (bool) ($data['can_approve_waivers'] ?? false),
                'can_approve_refunds' => (bool) ($data['can_approve_refunds'] ?? false),
                'max_waiver_amount' => $data['max_waiver_amount'] ?? null,
                'is_active' => true,
            ]);

            return redirect()->route('accountants.index')->with('success', 'Accountant created successfully.');
        });
    }

    public function show(Accountant $accountant)
    {
        $accountant->load(['user', 'approvedWaivers', 'collectedFees']);

        if (view()->exists('accountants.show')) {
            return view('accountants.show', compact('accountant'));
        }

        return response()->json([
            'message' => 'Accountant show view not implemented yet.',
            'accountant' => $accountant,
        ]);
    }

    public function edit(Accountant $accountant)
    {
        $accountant->load('user');

        if (view()->exists('accountants.edit')) {
            return view('accountants.edit', compact('accountant'));
        }

        return back()->with('info', 'Edit accountant form coming soon.');
    }

    public function update(UpdateAccountantRequest $request, Accountant $accountant)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $accountant) {
            // Update linked user
            $user = $accountant->user;
            if ($user) {
                $user->name = $data['name'];
                $user->email = $data['email'] ?? $user->email;
                $user->phone = $data['phone'] ?? $user->phone;
                if (!empty($data['password'])) {
                    $user->password = Hash::make($data['password']);
                }
                $user->save();

                // Ensure spatie role if available
                if (method_exists($user, 'assignRole')) {
                    try {
                        if (method_exists($user, 'hasRole') && !$user->hasRole('accountant')) {
                            $user->assignRole('accountant');
                        }
                    } catch (\Throwable $e) {
                        // ignore if roles not configured yet
                    }
                }
            }

            // Update accountant fields
            $accountant->update([
                'employee_id' => $data['employee_id'] ?? $accountant->employee_id,
                'designation' => $data['designation'] ?? $accountant->designation,
                'qualification' => $data['qualification'] ?? $accountant->qualification,
                'phone' => $data['phone'] ?? $accountant->phone,
                'address' => $data['address'] ?? $accountant->address,
                'joining_date' => $data['joining_date'] ?? $accountant->joining_date,
                'salary' => $data['salary'] ?? $accountant->salary,
                'can_approve_waivers' => (bool) ($data['can_approve_waivers'] ?? $accountant->can_approve_waivers),
                'can_approve_refunds' => (bool) ($data['can_approve_refunds'] ?? $accountant->can_approve_refunds),
                'max_waiver_amount' => $data['max_waiver_amount'] ?? $accountant->max_waiver_amount,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $accountant->is_active,
            ]);

            return redirect()->route('accountants.show', $accountant)->with('success', 'Accountant updated successfully.');
        });
    }

    public function destroy(Accountant $accountant)
    {
        // Soft behavior: deactivate instead of hard delete if has records
        if ($accountant->collectedFees()->exists() || $accountant->approvedWaivers()->exists()) {
            $accountant->update(['is_active' => false]);
            return back()->with('success', 'Accountant deactivated due to financial records.');
        }

        $accountant->delete();
        return back()->with('success', 'Accountant deleted successfully.');
    }
}
