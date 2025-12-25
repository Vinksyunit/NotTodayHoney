<?php

namespace Vinksyunit\NotTodayHoney\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'probing_count',
        'intrusion_attempt_count',
        'attacking_count',
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
        'probing_count' => 'integer',
        'intrusion_attempt_count' => 'integer',
        'attacking_count' => 'integer',
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
     * Increment the attempt count for a specific alert level.
     */
    public function incrementAttemptForLevel(AlertLevel $level): void
    {
        $field = match ($level) {
            AlertLevel::PROBING => 'probing_count',
            AlertLevel::INTRUSION_ATTEMPT => 'intrusion_attempt_count',
            AlertLevel::ATTACKING => 'attacking_count',
        };

        $this->increment($field);
    }

    /**
     * Get the attempt count for a specific alert level.
     */
    public function getCountForLevel(AlertLevel $level): int
    {
        return match ($level) {
            AlertLevel::PROBING => $this->probing_count,
            AlertLevel::INTRUSION_ATTEMPT => $this->intrusion_attempt_count,
            AlertLevel::ATTACKING => $this->attacking_count,
        };
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

    /**
     * Get the trap attempts for this attacker.
     */
    public function trapAttempts(): HasMany
    {
        return $this->hasMany(TrapAttempt::class);
    }

    /**
     * Get the credential attempts for this attacker.
     */
    public function credentialAttempts(): HasMany
    {
        return $this->hasMany(CredentialAttempt::class);
    }
}
