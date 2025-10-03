<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     */
    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * Check if the notice is expired.
     */
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if the notice is published.
     */
    public function getIsPublishedAttribute()
    {
        return $this->publish_date->isPast() && $this->is_active;
    }

    /**
     * Scope a query to only include active notices.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include published notices.
     */
    public function scopePublished($query)
    {
        return $query->where('publish_date', '<=', now())
            ->where('is_active', true);
    }

    /**
     * Scope a query to only include notices that are not expired.
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
     */
    public function scopeFor($query, $audience)
    {
        return $query->where(function ($q) use ($audience) {
            $q->where('notice_for', $audience)
                ->orWhere('notice_for', 'all');
        });
    }
}