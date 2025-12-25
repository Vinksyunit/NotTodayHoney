<?php

namespace Vinksyunit\NotTodayHoney\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'block_duration',
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
        'attempt_count' => 'integer',
        'block_duration' => 'integer',
    ];

    /**
     * Check if the attacker is currently blocked.
     */
    public function isBlocked(): bool
    {
        if (! $this->blocked_at || ! $this->block_duration) {
            return false;
        }

        $blockExpiry = $this->blocked_at->addMinutes($this->block_duration);

        return now()->lessThan($blockExpiry);
    }

    /**
     * Get the remaining block time in minutes.
     */
    public function getRemainingBlockTime(): ?int
    {
        if (! $this->isBlocked()) {
            return null;
        }

        $blockExpiry = $this->blocked_at->addMinutes($this->block_duration);

        return now()->diffInMinutes($blockExpiry, false);
    }

    /**
     * Increment the attempt count.
     */
    public function incrementAttempts(): void
    {
        $this->increment('attempt_count');
    }

    /**
     * Block the attacker for a specified duration.
     */
    public function block(int $durationInMinutes, string $alertLevel): void
    {
        $this->update([
            'blocked_at' => now(),
            'block_duration' => $durationInMinutes,
            'alert_level' => $alertLevel,
        ]);
    }

    /**
     * Scope to get only blocked attackers.
     */
    public function scopeBlocked($query)
    {
        return $query->whereNotNull('blocked_at')
            ->whereNotNull('block_duration')
            ->whereRaw('DATE_ADD(blocked_at, INTERVAL block_duration MINUTE) > NOW()');
    }

    /**
     * Scope to get attackers by alert level.
     */
    public function scopeByAlertLevel($query, string $level)
    {
        return $query->where('alert_level', $level);
    }
}
