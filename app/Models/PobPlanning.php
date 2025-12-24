<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PobPlanning extends Model
{
    use SoftDeletes;

    protected $table = 'pob_planning';

    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'location',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user who created this planning entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to check if a schedule is active on a specific date.
     */
    public function scopeActiveOnDate($query, $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            });
    }

    /**
     * Scope to find schedules that overlap with a date range.
     */
    public function scopeOverlapsDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $startDate);
            });
    }

    /**
     * Scope to filter by location.
     */
    public function scopeForLocation($query, $location)
    {
        if ($location) {
            return $query->where('location', $location);
        }
        return $query;
    }

    /**
     * Scope to get only active (non-cancelled) plans.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    /**
     * Check if this schedule is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        $today = now()->toDateString();
        return $this->start_date <= $today
            && ($this->end_date === null || $this->end_date >= $today);
    }

    /**
     * Get the duration in days.
     */
    public function getDurationAttribute(): ?int
    {
        if ($this->end_date === null) {
            return null;
        }
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
