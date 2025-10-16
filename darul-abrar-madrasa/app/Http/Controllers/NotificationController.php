<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\NotificationTrigger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display notification history.
     */
    public function index(Request $request)
    {
        try {
            // Admin only
            if (Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized access');
            }

            $filters = [
                'type' => $request->type,
                'status' => $request->status,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ];

            $notifications = $this->notificationService->getNotificationHistory($filters, 20);

            // Calculate summary stats
            $totalSent = Notification::sent()->count();
            $totalFailed = Notification::failed()->count();
            $totalPending = Notification::pending()->count();
            $successRate = $totalSent > 0 ? round(($totalSent / ($totalSent + $totalFailed)) * 100, 1) : 0;

            return view('notifications.index', compact(
                'notifications',
                'totalSent',
                'totalFailed',
                'totalPending',
                'successRate'
            ));
        } catch (\Exception $e) {
            Log::error('Failed to load notification history', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load notifications. Please try again.');
        }
    }

    /**
     * Display notification templates.
     */
    public function templates()
    {
        try {
            // Admin only
            if (Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized access');
            }

            $templates = NotificationTemplate::orderBy('type')->orderBy('channel')->get();

            return view('notifications.templates', compact('templates'));
        } catch (\Exception $e) {
            Log::error('Failed to load notification templates', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load templates. Please try again.');
        }
    }

    /**
     * Show template edit form.
     */
    public function editTemplate(NotificationTemplate $template)
    {
        try {
            // Admin only
            if (Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized access');
            }

            return view('notifications.edit-template', compact('template'));
        } catch (\Exception $e) {
            Log::error('Failed to load template edit form', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load template. Please try again.');
        }
    }

    /**
     * Update notification template.
     */
    public function updateTemplate(Request $request, NotificationTemplate $template)
    {
        try {
            // Admin only
            if (Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized access');
            }

            $validated = $request->validate([
                'subject' => 'nullable|string|max:255',
                'body' => 'required|string',
                'is_active' => 'boolean',
            ]);

            $template->update($validated);

            Log::info('Notification template updated', [
                'template_id' => $template->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('notifications.templates')
                ->with('success', 'Template updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update template', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->withInput()->with('error', 'Failed to update template. Please try again.');
        }
    }

    /**
     * Display notification triggers.
     */
    public function triggers()
    {
        try {
            // Admin only
            if (Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized access');
            }

            $triggers = NotificationTrigger::all();

            return view('notifications.triggers', compact('triggers'));
        } catch (\Exception $e) {
            Log::error('Failed to load notification triggers', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load triggers. Please try again.');
        }
    }

    /**
     * Update notification trigger.
     */
    public function updateTrigger(Request $request, NotificationTrigger $trigger)
    {
        try {
            // Admin only
            if (Auth::user()->role !== 'admin') {
                abort(403, 'Unauthorized access');
            }

            $validated = $request->validate([
                'is_enabled' => 'boolean',
                'conditions' => 'nullable|array',
            ]);

            $trigger->update($validated);

            Log::info('Notification trigger updated', [
                'trigger_id' => $trigger->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('notifications.triggers')
                ->with('success', 'Trigger updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update trigger', [
                'trigger_id' => $trigger->id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->withInput()->with('error', 'Failed to update trigger. Please try again.');
        }
    }

    /**
     * Send test notification.
     */
    public function testNotification(Request $request)
    {
        try {
            // Admin only
            if (Auth::user()->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'type' => 'required|string',
                'recipient_id' => 'required|integer',
                'channel' => 'required|in:email,sms,both',
            ]);

            $testData = [
                'student_name' => 'Test Student',
                'guardian_name' => 'Test Guardian',
                'test_mode' => true,
            ];

            $notificationId = $this->notificationService->sendNotification(
                $validated['type'],
                $validated['recipient_id'],
                'guardian',
                $testData,
                $validated['channel']
            );

            if ($notificationId) {
                Log::info('Test notification sent', [
                    'notification_id' => $notificationId,
                    'user_id' => Auth::id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Test notification sent successfully',
                    'notification_id' => $notificationId,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send test notification',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send test notification', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
