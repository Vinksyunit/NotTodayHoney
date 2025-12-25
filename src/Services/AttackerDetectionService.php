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
     * Record an attempt from an IP address.
     * This method will create or update the detection record and trigger appropriate events.
     */
    public function recordAttempt(string $ip, ?AlertLevel $forcedLevel = null): AttackerDetection
    {
        $ipHash = $this->hashIp($ip);

        // Find or create the detection record
        $detection = AttackerDetection::firstOrCreate(
            ['ip_hash' => $ipHash],
            [
                'ip' => $ip,
                'ip_hash' => $ipHash,
                'attempt_count' => 0,
                'first_attempt_at' => now(),
            ]
        );

        // If this is not a new record, increment the attempt count
        if (! $detection->wasRecentlyCreated) {
            $detection->incrementAttempts();
            $detection->refresh();
        }

        // Check and trigger appropriate alert based on thresholds or forced level
        $this->checkAndTriggerAlert($detection, $forcedLevel);

        return $detection;
    }

    /**
     * Record an attempt with a specific level (useful for attacking when leaked password detected).
     */
    public function recordAttemptWithLevel(string $ip, AlertLevel $level): AttackerDetection
    {
        return $this->recordAttempt($ip, $level);
    }

    /**
     * Check the attempt count and trigger the appropriate alert event.
     */
    protected function checkAndTriggerAlert(AttackerDetection $detection, ?AlertLevel $forcedLevel = null): void
    {
        $attemptCount = $detection->attempt_count;
        $config = config('not-today-honey.alerts');

        // If a level is forced (e.g., leaked password detected), use it
        if ($forcedLevel === AlertLevel::ATTACKING) {
            $this->triggerAttackingAlert($detection, $config['attacking']['duration']);

            return;
        }

        if ($forcedLevel === AlertLevel::INTRUSION_ATTEMPT) {
            $this->triggerIntrusionAttemptAlert($detection, $config['intrusion_attempt']['duration']);

            return;
        }

        // Check attacking threshold first
        if ($attemptCount >= $config['attacking']['threshold']) {
            $this->triggerAttackingAlert($detection, $config['attacking']['duration']);

            return;
        }

        // Check intrusion_attempt threshold
        if ($attemptCount >= $config['intrusion_attempt']['threshold']) {
            $this->triggerIntrusionAttemptAlert($detection, $config['intrusion_attempt']['duration']);

            return;
        }

        // Check probing threshold
        if ($attemptCount >= $config['probing']['threshold']) {
            $this->triggerProbingAlert($detection, $config['probing']['duration']);
        }
    }

    /**
     * Trigger a probing alert.
     */
    protected function triggerProbingAlert(AttackerDetection $detection, ?int $blockDuration): void
    {
        if ($detection->alert_level !== AlertLevel::PROBING) {
            if ($blockDuration !== null) {
                $detection->blockUntil(now()->addMinutes($blockDuration), AlertLevel::PROBING);
            }
            Event::dispatch(new AttackerProbingEvent($detection));
        }
    }

    /**
     * Trigger an intrusion attempt alert.
     */
    protected function triggerIntrusionAttemptAlert(AttackerDetection $detection, ?int $blockDuration): void
    {
        if ($detection->alert_level !== AlertLevel::INTRUSION_ATTEMPT && $detection->alert_level !== AlertLevel::ATTACKING) {
            if ($blockDuration !== null) {
                $detection->blockUntil(now()->addMinutes($blockDuration), AlertLevel::INTRUSION_ATTEMPT);
            }
            Event::dispatch(new AttackerIntrusionAttemptEvent($detection));
        }
    }

    /**
     * Trigger an attacking alert.
     */
    protected function triggerAttackingAlert(AttackerDetection $detection, ?int $blockDuration): void
    {
        if ($detection->alert_level !== AlertLevel::ATTACKING) {
            // Attacking can have null duration (permanent block)
            if ($blockDuration !== null) {
                $detection->blockUntil(now()->addMinutes($blockDuration), AlertLevel::ATTACKING);
            } else {
                // Permanent block: set to 100 years in the future
                $detection->blockUntil(now()->addYears(100), AlertLevel::ATTACKING);
            }
            Event::dispatch(new AttackerAttackingEvent($detection));
        }
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
