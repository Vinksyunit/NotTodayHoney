<?php

namespace Vinksyunit\NotTodayHoney\Services;

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
        $ipHash = $this->hashIp($ip);

        // Find or create the detection record
        $detection = AttackerDetection::firstOrCreate(
            ['ip_hash' => $ipHash],
            [
                'ip' => $ip,
                'ip_hash' => $ipHash,
                'alert_level' => $level,
            ]
        );

        // Check if the last attempt is outside the time window
        if ($detection->wasRecentlyCreated === false && $detection->isOutsideTimeWindow($level)) {
            $detection->resetCounters();
            $detection->refresh();
        }

        // Increment the counter for this specific level
        $detection->incrementAttemptForLevel($level);
        $detection->refresh();

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
        $count = $detection->getCountForLevel($level);

        // Trigger alert only when threshold is exactly reached (not on every subsequent attempt)
        if ($count === $threshold) {
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

        $detection = AttackerDetection::where('ip_hash', $ipHash)->first();

        if (! $detection) {
            return false;
        }

        return $detection->isBlocked();
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
    public function getBlockedIps(): \Illuminate\Database\Eloquent\Collection
    {
        return AttackerDetection::blocked()->get();
    }

    /**
     * Get detections by alert level.
     */
    public function getDetectionsByLevel(AlertLevel $level): \Illuminate\Database\Eloquent\Collection
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
