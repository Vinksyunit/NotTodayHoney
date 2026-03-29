<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerIntrusionAttemptEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerProbingEvent;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;

class AttackerDetectionService
{
    /**
     * Record an attempt from an IP address based on user action.
     *
     * @param  string  $ip  The IP address making the attempt
     * @param  AlertLevel  $level  The alert level based on user action (Probing, IntrusionAttempt, or Attacking)
     */
    public function recordAttempt(string $ip, AlertLevel $level): AttackerDetection
    {
        $whitelist = config('not-today-honey.whitelist', []);
        if (in_array($ip, $whitelist, true)) {
            return new AttackerDetection([
                'ip' => $ip,
                'ip_hash' => $this->hashIp($ip),
                'alert_level' => $level,
                'attempt_count' => 0,
            ]);
        }

        $ipHash = $this->hashIp($ip);
        $config = config('not-today-honey.alerts');
        $timeWindow = $config[$level->value]['time_window'] ?? 1440;

        // Try to find an existing detection for this IP, level, and within time window
        $detection = AttackerDetection::forIpAndLevel($ipHash, $level, $timeWindow);

        if ($detection instanceof AttackerDetection) {
            // Found existing detection within time window - increment counter
            $detection->increment('attempt_count');
            $detection->refresh();
        } else {
            // No detection found or outside time window - create new record
            $detection = AttackerDetection::create([
                'ip' => $ip,
                'ip_hash' => $ipHash,
                'alert_level' => $level,
                'attempt_count' => 1,
            ]);
        }

        // Check if threshold is reached and trigger alert
        $this->checkAndTriggerAlert($detection, $level);

        return $detection;
    }

    /**
     * Check if the threshold for this level is reached and trigger the appropriate alert.
     */
    protected function checkAndTriggerAlert(AttackerDetection $detection, AlertLevel $level): void
    {
        $config = config('not-today-honey.alerts');
        $levelKey = $level->value;
        $threshold = $config[$levelKey]['threshold'];

        // Trigger alert only when threshold is exactly reached (not on every subsequent attempt)
        if ($detection->attempt_count === $threshold) {
            $this->triggerAlert($detection, $level, $config[$levelKey]['duration']);
        }
    }

    /**
     * Trigger an alert for a specific level and block the IP.
     */
    protected function triggerAlert(AttackerDetection $detection, AlertLevel $level, ?int $blockDuration): void
    {
        // Block the IP
        if ($blockDuration !== null) {
            $detection->blockUntil(now()->addMinutes($blockDuration), $level);
        } else {
            // Permanent block (null duration): set to 100 years in the future
            $detection->blockUntil(now()->addYears(100), $level);
        }

        // Dispatch the appropriate event
        $eventClass = match ($level) {
            AlertLevel::PROBING => AttackerProbingEvent::class,
            AlertLevel::INTRUSION_ATTEMPT => AttackerIntrusionAttemptEvent::class,
            AlertLevel::ATTACKING => AttackerAttackingEvent::class,
        };

        Event::dispatch(new $eventClass($detection));
    }

    /**
     * Hash an IP address for anonymization.
     */
    protected function hashIp(string $ip): string
    {
        return hash('sha256', $ip);
    }

    /**
     * Check if an IP is currently blocked.
     */
    public function isBlocked(string $ip): bool
    {
        $ipHash = $this->hashIp($ip);

        // Check if any detection record for this IP is currently blocked
        return AttackerDetection::where('ip_hash', $ipHash)
            ->whereNotNull('blocked_until')
            ->where('blocked_until', '>', now())
            ->exists();
    }

    /**
     * Get the detection record for an IP address.
     */
    public function getDetection(string $ip): ?AttackerDetection
    {
        $ipHash = $this->hashIp($ip);

        return AttackerDetection::where('ip_hash', $ipHash)->first();
    }

    /**
     * Get all currently blocked IPs.
     */
    public function getBlockedIps(): Collection
    {
        return AttackerDetection::blocked()->get();
    }

    /**
     * Get detections by alert level.
     */
    public function getDetectionsByLevel(AlertLevel $level): Collection
    {
        return AttackerDetection::byAlertLevel($level)->get();
    }

    /**
     * Reset a detection record (useful for testing or manual intervention).
     */
    public function resetDetection(string $ip): void
    {
        $ipHash = $this->hashIp($ip);

        AttackerDetection::where('ip_hash', $ipHash)->delete();
    }
}
