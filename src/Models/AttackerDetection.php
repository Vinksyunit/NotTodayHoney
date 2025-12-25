<?php

namespace Vinksyunit\NotTodayHoney\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;

class AttackerDetection extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nt_honey_attacker_detections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ip',
        'ip_hash',
        'attempt_count',
        'first_attempt_at',
        'blocked_at',
        'blocked_until',
        'alert_level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'first_attempt_at' => 'datetime',
        'blocked_at' => 'datetime',
        'blocked_until' => 'datetime',
        'attempt_count' => 'integer',
        'alert_level' => AlertLevel::class,
    ];

    /**
     * Check if the attacker is currently blocked.
     */
    public function isBlocked(): bool
    {
        if (! $this->blocked_until) {
            return false;
        }

        return now()->lessThan($this->blocked_until);
    }

    /**
     * Get the remaining block time in minutes.
     */
    public function getRemainingBlockTime(): ?int
    {
        if (! $this->isBlocked()) {
            return null;
        }

        return now()->diffInMinutes($this->blocked_until, false);
    }

    /**
     * Increment the attempt count.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempt_count');
    }

    /**
     * Block the attacker until a specific timestamp.
     */
    public function blockUntil(\DateTimeInterface $blockedUntil, AlertLevel $alertLevel): void
    {
        $this->update([
            'blocked_at' => now(),
            'blocked_until' => $blockedUntil,
            'alert_level' => $alertLevel,
        ]);
    }

    /**
     * Scope to get only blocked attackers.
     */
    public function scopeBlocked($query)
    {
        return $query->whereNotNull('blocked_until')
            ->where('blocked_until', '>', now());
    }

    /**
     * Scope to get attackers by alert level.
     */
    public function scopeByAlertLevel($query, AlertLevel $level)
    {
        return $query->where('alert_level', $level);
    }
}
