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
                'first_attempt_at' => now(),
                'alert_level' => $level,
            ]
        );

        // Increment the counter for this specific level
        $detection->incrementAttemptForLevel($level);
        $detection->refresh();

        // Update alert_level to the highest level seen
        $this->updateAlertLevel($detection, $level);

        // Check if this level's threshold is reached and trigger event
        $this->checkAndTriggerAlert($detection, $level);

        return $detection;
    }

    /**
     * Update the alert level to the highest level encountered.
     */
    protected function updateAlertLevel(AttackerDetection $detection, AlertLevel $newLevel): void
    {
        $levelPriority = [
            AlertLevel::PROBING->value => 1,
            AlertLevel::INTRUSION_ATTEMPT->value => 2,
            AlertLevel::ATTACKING->value => 3,
        ];

        $currentPriority = $levelPriority[$detection->alert_level->value];
        $newPriority = $levelPriority[$newLevel->value];

        if ($newPriority > $currentPriority) {
            $detection->update(['alert_level' => $newLevel]);
        }
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
            $duration = $config[$levelKey]['duration'];

            match ($level) {
                AlertLevel::PROBING => $this->triggerProbingAlert($detection, $duration),
                AlertLevel::INTRUSION_ATTEMPT => $this->triggerIntrusionAttemptAlert($detection, $duration),
                AlertLevel::ATTACKING => $this->triggerAttackingAlert($detection, $duration),
            };
        }
    }

    /**
     * Trigger a probing alert and block the IP.
     */
    protected function triggerProbingAlert(AttackerDetection $detection, ?int $blockDuration): void
    {
        if ($blockDuration !== null) {
            $detection->blockUntil(now()->addMinutes($blockDuration), $detection->alert_level);
        }

        Event::dispatch(new AttackerProbingEvent($detection));
    }

    /**
     * Trigger an intrusion attempt alert and block the IP.
     */
    protected function triggerIntrusionAttemptAlert(AttackerDetection $detection, ?int $blockDuration): void
    {
        if ($blockDuration !== null) {
            $detection->blockUntil(now()->addMinutes($blockDuration), $detection->alert_level);
        }

        Event::dispatch(new AttackerIntrusionAttemptEvent($detection));
    }

    /**
     * Trigger an attacking alert and block the IP.
     */
    protected function triggerAttackingAlert(AttackerDetection $detection, ?int $blockDuration): void
    {
        // Attacking can have null duration (permanent block)
        if ($blockDuration !== null) {
            $detection->blockUntil(now()->addMinutes($blockDuration), $detection->alert_level);
        } else {
            // Permanent block: set to 100 years in the future
            $detection->blockUntil(now()->addYears(100), $detection->alert_level);
        }

        Event::dispatch(new AttackerAttackingEvent($detection));
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
