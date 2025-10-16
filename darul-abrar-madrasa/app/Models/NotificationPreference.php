<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NotificationPreference Model
 * 
 * @property int $id
 * @property int $guardian_id
 * @property string $notification_type
 * @property bool $email_enabled
 * @property bool $sms_enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NotificationPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'guardian_id',
        'notification_type',
        'email_enabled',
        'sms_enabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
    ];

    /**
     * Get the guardian that owns this preference.
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    /**
     * Check if email notifications are enabled.
     */
    public function isEmailEnabled(): bool
    {
        return $this->email_enabled;
    }

    /**
     * Check if SMS notifications are enabled.
     */
    public function isSmsEnabled(): bool
    {
        return $this->sms_enabled;
    }

    /**
     * Check if specific channel is enabled.
     */
    public function isEnabled(string $channel): bool
    {
        return match($channel) {
            'email' => $this->email_enabled,
            'sms' => $this->sms_enabled,
            'both' => $this->email_enabled || $this->sms_enabled,
            default => false,
        };
    }
}
