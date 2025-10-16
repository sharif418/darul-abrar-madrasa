<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * The file upload service instance.
     */
    protected FileUploadService $fileUploadService;

    /**
     * Create a new controller instance.
     */
    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display the user's profile.
     */
    public function show()
    {
        try {
            $user = Auth::user();

            // Load role-specific relationships based on user role
            if ($user->isTeacher()) {
                $user->load([
                    'teacher.department',
                    'teacher.subjects.class'
                ]);
            } elseif ($user->isStudent()) {
                $user->load([
                    'student.class',
                    'student.guardians',
                    'student.attendances',
                    'student.fees',
                    'student.results'
                ]);
            } elseif ($user->isGuardian()) {
                $user->load([
                    'guardian.students.class'
                ]);
            } elseif ($user->isAccountant()) {
                $user->load([
                    'accountant.approvedWaivers',
                    'accountant.collectedFees'
                ]);
            }

            return view('profile.show', compact('user'));
        } catch (\Exception $e) {
            Log::error('Error loading user profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            return redirect()->back()->with('error', 'Failed to load profile. Please try again.');
        }
    }

    /**
     * Update the user's profile information.
     */
    public function update(UpdateProfileRequest $request)
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            // Handle avatar upload if present
            if ($request->hasFile('avatar')) {
                $avatarPath = $this->fileUploadService->uploadAvatar($request->file('avatar'));

                if ($avatarPath) {
                    // Delete old avatar if exists
                    if ($user->avatar) {
                        $this->fileUploadService->deleteFile($user->avatar);
                    }

                    $data['avatar'] = $avatarPath;
                }
            }

            // Update user record
            $user->update($data);

            Log::info('User profile updated successfully', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($data)
            ]);

            return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating user profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $user = Auth::user();

            // Update user's password (hashing handled by model cast)
            $user->update(['password' => $request->password]);

            // Logout other devices for security
            Auth::logoutOtherDevices($request->password);

            // Regenerate remember token
            $user->setRememberToken(Str::random(60));
            $user->save();

            // Invalidate session and regenerate CSRF token for security
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::info('User password updated successfully', [
                'user_id' => $user->id
            ]);

            return redirect()->route('profile.show')->with('success', 'Password updated successfully. Other devices have been logged out.');
        } catch (\Exception $e) {
            Log::error('Error updating user password: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            return redirect()->back()->with('error', 'Failed to update password. Please try again.');
        }
    }
}
