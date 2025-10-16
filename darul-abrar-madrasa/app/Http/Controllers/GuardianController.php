<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuardianRequest;
use App\Http\Requests\UpdateGuardianRequest;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuardianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
        $query = Guardian::with(['user', 'students']);
        if ($request->filled('active')) {
            $query->where('is_active', (bool) $request->boolean('active'));
        }
        if ($request->filled('q')) {
            $q = '%' . $request->input('q') . '%';
            $query->whereHas('user', function ($sub) use ($q) {
                $sub->where('name', 'like', $q)
                    ->orWhere('email', 'like', $q);
            })->orWhere('phone', 'like', $q);
        }
        $guardians = $query->paginate(20);

        if (view()->exists('guardians.index')) {
            return view('guardians.index', compact('guardians'));
        }

        return response()->json([
            'message' => 'Guardians index view not implemented yet.',
            'total' => $guardians->total(),
        ]);
    }

    public function create()
    {
        if (view()->exists('guardians.create')) {
            return view('guardians.create');
        }

        return back()->with('info', 'Create guardian form coming soon.');
    }

    public function store(StoreGuardianRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            // Prepare user fields
            $email = $data['email'] ?? null;
            if (empty($email)) {
                $email = 'guardian_' . Str::uuid()->toString() . '@temp.darulabrar.edu';
            }

            // Create user with role guardian
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make($data['password']),
                'role' => 'guardian',
                'phone' => $data['phone'],
                'is_active' => true,
            ]);

            // Assign spatie role if available
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('guardian');
                } catch (\Throwable $e) {
                    // ignore if roles not configured yet
                }
            }

            // Create guardian record
            $guardian = Guardian::create([
                'user_id' => $user->id,
                'national_id' => $data['national_id'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'address' => $data['address'],
                'phone' => $data['phone'],
                'alternative_phone' => $data['alternative_phone'] ?? null,
                'email' => $email,
                'relationship_type' => $data['relationship_type'],
                'is_primary_contact' => (bool) ($data['is_primary_contact'] ?? true),
                'emergency_contact' => (bool) ($data['emergency_contact'] ?? false),
                'is_active' => true,
            ]);

            // Optionally link to students
            if (!empty($data['student_ids']) && is_array($data['student_ids'])) {
                $pivotDefaults = [
                    'relationship_type' => $data['relationship_type'] ?? 'other',
                    'is_primary_guardian' => (bool) ($data['is_primary_contact'] ?? true),
                    'can_pickup' => true,
                    'financial_responsibility' => false,
                    'receive_notifications' => true,
                    'notes' => null,
                ];

                $syncPayload = [];
                foreach ($data['student_ids'] as $sid) {
                    $syncPayload[(int) $sid] = $pivotDefaults;
                }

                $guardian->students()->syncWithoutDetaching($syncPayload);
            }

            return redirect()->route('guardians.index')->with('success', 'Guardian created successfully.');
        });
    }

    public function show(Guardian $guardian)
    {
        $guardian->load(['user', 'students.user', 'students.class']);

        if (view()->exists('guardians.show')) {
            return view('guardians.show', compact('guardian'));
        }

        return response()->json([
            'message' => 'Guardian show view not implemented yet.',
            'guardian' => $guardian,
        ]);
    }

    public function edit(Guardian $guardian)
    {
        $guardian->load('user');

        if (view()->exists('guardians.edit')) {
            return view('guardians.edit', compact('guardian'));
        }

        return back()->with('info', 'Edit guardian form coming soon.');
    }

    public function update(UpdateGuardianRequest $request, Guardian $guardian)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $guardian) {
            // Update linked user
            $user = $guardian->user;
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
                        if (method_exists($user, 'hasRole') && !$user->hasRole('guardian')) {
                            $user->assignRole('guardian');
                        }
                    } catch (\Throwable $e) {
                        // ignore if roles not configured yet
                    }
                }
            }

            // Update guardian fields
            $guardian->update([
                'national_id' => $data['national_id'] ?? $guardian->national_id,
                'occupation' => $data['occupation'] ?? $guardian->occupation,
                'address' => $data['address'] ?? $guardian->address,
                'phone' => $data['phone'] ?? $guardian->phone,
                'alternative_phone' => $data['alternative_phone'] ?? $guardian->alternative_phone,
                'email' => $data['email'] ?? $guardian->email,
                'relationship_type' => $data['relationship_type'] ?? $guardian->relationship_type,
                'is_primary_contact' => (bool) ($data['is_primary_contact'] ?? $guardian->is_primary_contact),
                'emergency_contact' => (bool) ($data['emergency_contact'] ?? $guardian->emergency_contact),
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $guardian->is_active,
            ]);

            return redirect()->route('guardians.show', $guardian)->with('success', 'Guardian updated successfully.');
        });
    }

    public function destroy(Guardian $guardian)
    {
        // Soft behavior: set inactive if has links; otherwise delete
        if ($guardian->students()->exists()) {
            $guardian->update(['is_active' => false]);
            return back()->with('success', 'Guardian deactivated because it has linked students.');
        }

        $guardian->delete();
        return back()->with('success', 'Guardian deleted successfully.');
    }

    public function linkStudent($guardianId, Request $request)
    {
        $guardian = Guardian::findOrFail($guardianId);

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'relationship_type' => 'nullable|in:father,mother,legal_guardian,sibling,other',
            'is_primary_guardian' => 'sometimes|boolean',
            'can_pickup' => 'sometimes|boolean',
            'financial_responsibility' => 'sometimes|boolean',
            'receive_notifications' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ]);

        $pivot = [
            'relationship_type' => $data['relationship_type'] ?? 'other',
            'is_primary_guardian' => (bool) ($data['is_primary_guardian'] ?? false),
            'can_pickup' => (bool) ($data['can_pickup'] ?? true),
            'financial_responsibility' => (bool) ($data['financial_responsibility'] ?? false),
            'receive_notifications' => (bool) ($data['receive_notifications'] ?? true),
            'notes' => $data['notes'] ?? null,
        ];

        $guardian->students()->syncWithoutDetaching([
            (int) $data['student_id'] => $pivot,
        ]);

        return back()->with('success', 'Student linked to guardian successfully.');
    }

    public function unlinkStudent($guardianId, $studentId)
    {
        $guardian = Guardian::findOrFail($guardianId);
        $student = Student::findOrFail($studentId);

        $guardian->students()->detach($student->id);

        return back()->with('success', 'Student unlinked from guardian successfully.');
    }
}
