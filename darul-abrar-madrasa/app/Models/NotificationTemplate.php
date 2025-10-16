<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * NotificationTemplate Model
 * 
 * @property int $id
 * @property string $type
 * @property string $channel
 * @property string $name
 * @property string|null $subject
 * @property string $body
 * @property array|null $available_variables
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NotificationTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'type',
        'channel',
        'name',
        'subject',
        'body',
        'available_variables',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'available_variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by channel.
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Render template body with data.
     */
    public function render(array $data): string
    {
        $body = $this->body;
        
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $body = str_replace($placeholder, $value, $body);
        }
        
        return $body;
    }

    /**
     * Render template subject with data.
     */
    public function renderSubject(array $data): ?string
    {
        if (!$this->subject) {
            return null;
        }
        
        $subject = $this->subject;
        
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
        }
        
        return $subject;
    }

    /**
     * Get available variables for this template.
     */
    public function getAvailableVariables(): array
    {
        return $this->available_variables ?? [];
    }
}
