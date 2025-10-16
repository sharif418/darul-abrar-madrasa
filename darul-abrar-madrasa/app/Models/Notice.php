<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Notice
 * 
 * @property int $id
 * @property string $title
 * @property string $description
 * @property \Carbon\Carbon $publish_date
 * @property \Carbon\Carbon|null $expiry_date
 * @property string $notice_for
 * @property bool $is_active
 * @property int $published_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property-read User $publishedBy
 * @property-read bool $is_expired
 * @property-read bool $is_published
 */
class Notice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'publish_date',
        'expiry_date',
        'notice_for',
        'is_active',
        'published_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'publish_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who published the notice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Check if the notice is expired.
     *
     * @return bool
     */
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if the notice is published.
     *
     * @return bool
     */
    public function getIsPublishedAttribute()
    {
        return $this->publish_date->isPast() && $this->is_active;
    }

    /**
     * Scope a query to only include active notices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include published notices.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('publish_date', '<=', now())
            ->where('is_active', true);
    }

    /**
     * Scope a query to only include notices that are not expired.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
                ->orWhere('expiry_date', '>=', now());
        });
    }

    /**
     * Scope a query to only include notices for a specific audience.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $audience
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFor($query, $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('notice_for', $audience)
                ->orWhere('notice_for', 'all');
        });
    }

    /**
     * Scope a query to search by title or description.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     * @param string $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('publish_date', [$from, $to]);
    }

    /**
     * Check if notice can be edited.
     *
     * @return bool
     */
    public function canBeEdited(): bool
    {
        return !$this->is_expired;
    }

    /**
     * Check if notice can be deleted.
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return true; // Notices can always be deleted
    }

    /**
     * Get human-readable target audience.
     *
     * @return string
     */
    public function getTargetAudience(): string
    {
        return match($this->notice_for) {
            'all' => 'All Users',
            'students' => 'Students',
            'teachers' => 'Teachers',
            'staff' => 'Staff',
            default => ucfirst($this->notice_for),
        };
    }
}
