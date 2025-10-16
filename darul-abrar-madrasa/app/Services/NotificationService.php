<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\NotificationPreference;
use App\Models\Guardian;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * NotificationService
 * 
 * Handles sending SMS and Email notifications to guardians and students.
 * Follows the service pattern established by ActivityLogService and FileUploadService.
 */
class NotificationService
{
    /**
     * Send a notification to a recipient.
     *
     * @param string $type Notification type (low_attendance, poor_performance, etc.)
     * @param int $recipientId Recipient ID
     * @param string $recipientType Recipient type (guardian, student)
     * @param array $data Context data for template rendering
     * @param string $channel Channel to send (email, sms, both)
     * @return int|null Notification ID or null on failure
     */
    public function sendNotification(
        string $type,
        int $recipientId,
        string $recipientType,
        array $data,
        string $channel = 'both'
    ): ?int {
        try {
            // Get recipient contact info
            $contactInfo = $this->getRecipientContactInfo($recipientId, $recipientType);
            
            if (!$contactInfo) {
                Log::warning('Notification recipient not found', [
                    'type' => $type,
                    'recipient_id' => $recipientId,
                    'recipient_type' => $recipientType,
                ]);
                return null;
            }

            // Check preferences if recipient is guardian
            if ($recipientType === 'guardian') {
                if (!$this->checkPreferences($recipientId, $type, $channel)) {
                    Log::info('Notification skipped due to preferences', [
                        'type' => $type,
                        'guardian_id' => $recipientId,
                        'channel' => $channel,
                    ]);
                    return null;
                }
            }

            // If channel is 'both', create separate notifications for email and SMS
            if ($channel === 'both') {
                $emailId = null;
                $smsId = null;
                
                if ($contactInfo['email']) {
                    $emailId = $this->sendNotification($type, $recipientId, $recipientType, $data, 'email');
                }
                
                if ($contactInfo['phone']) {
                    $smsId = $this->sendNotification($type, $recipientId, $recipientType, $data, 'sms');
                }
                
                // Return the first successful notification ID
                return $emailId ?? $smsId;
            }

            // Create notification record for single channel
            $notification = Notification::create([
                'type' => $type,
                'channel' => $channel,
                'recipient_id' => $recipientId,
                'recipient_type' => $recipientType,
                'recipient_email' => $contactInfo['email'] ?? null,
                'recipient_phone' => $contactInfo['phone'] ?? null,
                'subject' => null, // Will be set when sending
                'message' => '', // Will be set when sending
                'data' => $data,
                'status' => Notification::STATUS_QUEUED,
                'triggered_by' => auth()->id(),
            ]);

            // Send via appropriate channel
            $success = false;
            
            if ($channel === 'email') {
                if ($contactInfo['email']) {
                    $emailTemplate = $this->getTemplate($type, 'email');
                    $subject = $emailTemplate->renderSubject($data);
                    $body = $emailTemplate->render($data);
                    
                    $notification->update(['subject' => $subject, 'message' => $body]);
                    
                    if ($this->sendEmail($contactInfo['email'], $subject, $body, $notification->id)) {
                        $success = true;
                    }
                }
            } elseif ($channel === 'sms') {
                if ($contactInfo['phone']) {
                    $smsTemplate = $this->getTemplate($type, 'sms');
                    $message = $smsTemplate->render($data);
                    
                    $notification->update(['message' => $message]);
                    
                    if ($this->sendSms($contactInfo['phone'], $message, $notification->id)) {
                        $success = true;
                    }
                }
            }

            if ($success) {
                Log::info('Notification sent successfully', [
                    'notification_id' => $notification->id,
                    'type' => $type,
                    'channel' => $channel,
                ]);
            }

            return $notification->id;
        } catch (Exception $e) {
            Log::error('Failed to send notification', [
                'type' => $type,
                'recipient_id' => $recipientId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Send email notification.
     *
     * @param string $to Email address
     * @param string $subject Email subject
     * @param string $body Email body
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public function sendEmail(string $to, string $subject, string $body, int $notificationId): bool
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $notification = Notification::find($notificationId);
            if ($notification) {
                $notification->markAsSent();
            }

            Log::info('Email sent successfully', [
                'notification_id' => $notificationId,
                'to' => $to,
            ]);

            return true;
        } catch (Exception $e) {
            $notification = Notification::find($notificationId);
            if ($notification) {
                $notification->markAsFailed('Email sending failed: ' . $e->getMessage());
            }

            Log::error('Failed to send email', [
                'notification_id' => $notificationId,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send SMS notification.
     *
     * @param string $to Phone number
     * @param string $message SMS message
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public function sendSms(string $to, string $message, int $notificationId): bool
    {
        try {
            // TODO: Integrate SMS provider (Twilio, Nexmo, etc.)
            // Check for SMS configuration
            $smsProvider = config('services.sms.provider');
            
            if (!$smsProvider) {
                Log::warning('SMS provider not configured', [
                    'notification_id' => $notificationId,
                ]);
                
                $notification = Notification::find($notificationId);
                if ($notification) {
                    $notification->markAsFailed('SMS provider not configured');
                }
                
                return false;
            }

            // Placeholder for SMS sending logic
            // When SMS provider is configured, implement actual sending here
            // Example for Twilio:
            // $twilio = new Client(config('services.sms.twilio.sid'), config('services.sms.twilio.token'));
            // $twilio->messages->create($to, ['from' => config('services.sms.twilio.from'), 'body' => $message]);

            Log::info('SMS sending attempted (provider not configured)', [
                'notification_id' => $notificationId,
                'to' => $to,
            ]);

            return false;
        } catch (Exception $e) {
            $notification = Notification::find($notificationId);
            if ($notification) {
                $notification->markAsFailed('SMS sending failed: ' . $e->getMessage());
            }

            Log::error('Failed to send SMS', [
                'notification_id' => $notificationId,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get notification template.
     *
     * @param string $type Notification type
     * @param string $channel Channel (email or sms)
     * @return NotificationTemplate
     */
    public function getTemplate(string $type, string $channel): NotificationTemplate
    {
        $template = NotificationTemplate::active()
            ->where('type', $type)
            ->where('channel', $channel)
            ->first();

        if (!$template) {
            // Return default template
            $template = new NotificationTemplate([
                'type' => $type,
                'channel' => $channel,
                'name' => "Default $type template",
                'subject' => $channel === 'email' ? "Notification: $type" : null,
                'body' => 'Default notification message. Please configure templates.',
                'is_active' => true,
            ]);
        }

        return $template;
    }

    /**
     * Check notification preferences.
     *
     * @param int $guardianId Guardian ID
     * @param string $type Notification type
     * @param string $channel Channel
     * @return bool Whether notifications are enabled
     */
    public function checkPreferences(int $guardianId, string $type, string $channel): bool
    {
        $preference = NotificationPreference::where('guardian_id', $guardianId)
            ->where('notification_type', $type)
            ->first();

        // If no preference exists, default to enabled
        if (!$preference) {
            return true;
        }

        return $preference->isEnabled($channel);
    }

    /**
     * Get recipient contact information.
     *
     * @param int $recipientId Recipient ID
     * @param string $recipientType Recipient type
     * @return array|null Contact info (email, phone) or null
     */
    protected function getRecipientContactInfo(int $recipientId, string $recipientType): ?array
    {
        if ($recipientType === 'guardian') {
            $guardian = Guardian::find($recipientId);
            if (!$guardian) {
                return null;
            }
            
            return [
                'email' => $guardian->email,
                'phone' => $guardian->phone,
            ];
        }

        // Add other recipient types as needed
        return null;
    }

    /**
     * Send bulk notifications.
     *
     * @param string $type Notification type
     * @param array $recipients Array of recipient data
     * @param array $data Common data for all notifications
     * @param string $channel Channel
     * @return array Array of notification IDs
     */
    public function sendBulkNotifications(
        string $type,
        array $recipients,
        array $data,
        string $channel = 'both'
    ): array {
        $notificationIds = [];

        foreach ($recipients as $recipient) {
            $recipientData = array_merge($data, $recipient['data'] ?? []);
            
            $notificationId = $this->sendNotification(
                $type,
                $recipient['id'],
                $recipient['type'] ?? 'guardian',
                $recipientData,
                $channel
            );

            if ($notificationId) {
                $notificationIds[] = $notificationId;
            }
        }

        Log::info('Bulk notifications sent', [
            'type' => $type,
            'total_recipients' => count($recipients),
            'successful' => count($notificationIds),
        ]);

        return $notificationIds;
    }

    /**
     * Get notification history with filters.
     *
     * @param array $filters Filters (type, status, date_from, date_to, recipient_id)
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getNotificationHistory(array $filters = [], int $perPage = 20)
    {
        $query = Notification::query()->with(['recipient', 'triggeredBy']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['recipient_id'])) {
            $query->where('recipient_id', $filters['recipient_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
