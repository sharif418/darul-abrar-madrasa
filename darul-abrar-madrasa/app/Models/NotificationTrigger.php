<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * NotificationTrigger Model
 * 
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string|null $description
 * @property bool $is_enabled
 * @property array|null $conditions
 * @property string $frequency
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class NotificationTrigger extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'type',
        'name',
        'description',
        'is_enabled',
        'conditions',
        'frequency',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'conditions' => 'array',
    ];

    /**
     * Scope a query to only include enabled triggers.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get a specific condition value.
     */
    public function getCondition(string $key, $default = null)
    {
        return $this->conditions[$key] ?? $default;
    }

    /**
     * Set a specific condition value.
     */
    public function setCondition(string $key, $value): void
    {
        $conditions = $this->conditions ?? [];
        $conditions[$key] = $value;
        $this->conditions = $conditions;
    }

    /**
     * Check if trigger is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }
}
