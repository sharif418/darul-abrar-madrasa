<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class GuardianController extends Controller
{
    public function index()
    {
        $guardians = Guardian::with(['students', 'user'])
            ->paginate(20);

        return view('guardians.index', compact('guardians'));
    }

    public function create()
    {
        $students = Student::with('user')->where('is_active', true)->get();

        return view('guardians.create', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:guardians,email',
            'phone' => 'required|string|max:15',
            'national_id' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'office_address' => 'nullable|string',
            'present_address' => 'required|string',
            'permanent_address' => 'nullable|string',
            'annual_income' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
            'create_user_account' => 'nullable|boolean',
            'password' => 'required_if:create_user_account,1|nullable|string|min:8',
            'students' => 'nullable|array',
            'students.*.id' => 'exists:students,id',
            'students.*.relationship' => 'required_with:students.*.id|in:father,mother,brother,sister,uncle,aunt,grandfather,grandmother,other',
            'students.*.is_primary' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $userId = null;

            if ($request->create_user_account && $request->email) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => 'guardian',
                    'phone' => $validated['phone'],
                    'is_active' => true,
                ]);

                $userId = $user->id;
            }

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('guardians', 'public');
            }

            $guardian = Guardian::create([
                'user_id' => $userId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'national_id' => $validated['national_id'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'designation' => $validated['designation'] ?? null,
                'office_address' => $validated['office_address'] ?? null,
                'present_address' => $validated['present_address'],
                'permanent_address' => $validated['permanent_address'] ?? $validated['present_address'],
                'annual_income' => $validated['annual_income'] ?? null,
                'photo' => $photoPath,
                'is_active' => true,
            ]);

            if (!empty($request->students)) {
                foreach ($request->students as $studentData) {
                    $guardian->students()->attach($studentData['id'], [
                        'relationship' => $studentData['relationship'],
                        'is_primary' => $studentData['is_primary'] ?? false,
                        'can_pickup' => true,
                        'receive_communication' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('guardians.index')
                ->with('success', 'Guardian created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            return back()->withInput()
                ->with('error', 'Failed to create guardian: ' . $e->getMessage());
        }
    }

    public function show(Guardian $guardian)
    {
        $guardian->load(['students.user', 'students.class', 'user']);

        return view('guardians.show', compact('guardian'));
    }

    public function edit(Guardian $guardian)
    {
        $guardian->load(['students']);
        $students = Student::with('user')->where('is_active', true)->get();

        return view('guardians.edit', compact('guardian', 'students'));
    }

    public function update(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:guardians,email,' . $guardian->id,
            'phone' => 'required|string|max:15',
            'national_id' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'office_address' => 'nullable|string',
            'present_address' => 'required|string',
            'permanent_address' => 'nullable|string',
            'annual_income' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|max:2048',
            'is_active' => 'required|boolean',
            'students' => 'nullable|array',
            'students.*.id' => 'exists:students,id',
            'students.*.relationship' => 'required_with:students.*.id|in:father,mother,brother,sister,uncle,aunt,grandfather,grandmother,other',
            'students.*.is_primary' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('photo')) {
                if ($guardian->photo) {
                    Storage::disk('public')->delete($guardian->photo);
                }

                $validated['photo'] = $request->file('photo')->store('guardians', 'public');
            }

            $guardian->update($validated);

            $guardian->students()->detach();

            if (!empty($request->students)) {
                foreach ($request->students as $studentData) {
                    $guardian->students()->attach($studentData['id'], [
                        'relationship' => $studentData['relationship'],
                        'is_primary' => $studentData['is_primary'] ?? false,
                        'can_pickup' => true,
                        'receive_communication' => true,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('guardians.show', $guardian)
                ->with('success', 'Guardian updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Failed to update guardian: ' . $e->getMessage());
        }
    }

    public function destroy(Guardian $guardian)
    {
        try {
            if ($guardian->photo) {
                Storage::disk('public')->delete($guardian->photo);
            }

            if ($guardian->user_id) {
                User::find($guardian->user_id)->delete();
            }

            $guardian->delete();

            return redirect()->route('guardians.index')
                ->with('success', 'Guardian deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete guardian: ' . $e->getMessage());
        }
    }
}
