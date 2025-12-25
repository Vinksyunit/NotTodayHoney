<?php

namespace Vinksyunit\NotTodayHoney\Services;

use Illuminate\Support\Facades\Event;
use Vinksyunit\NotTodayHoney\Events\AttackerLevel1Event;
use Vinksyunit\NotTodayHoney\Events\AttackerLevel2Event;
use Vinksyunit\NotTodayHoney\Events\AttackerLevel3Event;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;

class AttackerDetectionService
{
    /**
     * Record an attempt from an IP address.
     * This method will create or update the detection record and trigger appropriate events.
     */
    public function recordAttempt(string $ip, string $level = 'level_1'): AttackerDetection
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
        $this->checkAndTriggerAlert($detection, $level);

        return $detection;
    }

    /**
     * Record an attempt with a specific level (useful for level 3 when leaked password detected).
     */
    public function recordAttemptWithLevel(string $ip, string $level): AttackerDetection
    {
        return $this->recordAttempt($ip, $level);
    }

    /**
     * Check the attempt count and trigger the appropriate alert event.
     */
    protected function checkAndTriggerAlert(AttackerDetection $detection, ?string $forcedLevel = null): void
    {
        $attemptCount = $detection->attempt_count;
        $config = config('not-today-honey.alerts');

        // If a level is forced (e.g., leaked password detected), use it
        if ($forcedLevel === 'level_3') {
            $this->triggerLevel3Alert($detection, $config['level_3']['duration']);

            return;
        }

        if ($forcedLevel === 'level_2') {
            $this->triggerLevel2Alert($detection, $config['level_2']['duration']);

            return;
        }

        // Check level 3 threshold first
        if ($attemptCount >= $config['level_3']['threshold']) {
            $this->triggerLevel3Alert($detection, $config['level_3']['duration']);

            return;
        }

        // Check level 2 threshold
        if ($attemptCount >= $config['level_2']['threshold']) {
            $this->triggerLevel2Alert($detection, $config['level_2']['duration']);

            return;
        }

        // Check level 1 threshold
        if ($attemptCount >= $config['level_1']['threshold']) {
            $this->triggerLevel1Alert($detection, $config['level_1']['duration']);
        }
    }

    /**
     * Trigger a level 1 alert.
     */
    protected function triggerLevel1Alert(AttackerDetection $detection, ?int $blockDuration): void
    {
        if ($detection->alert_level !== 'level_1') {
            if ($blockDuration !== null) {
                $detection->block($blockDuration, 'level_1');
            }
            Event::dispatch(new AttackerLevel1Event($detection));
        }
    }

    /**
     * Trigger a level 2 alert.
     */
    protected function triggerLevel2Alert(AttackerDetection $detection, ?int $blockDuration): void
    {
        if ($detection->alert_level !== 'level_2' && $detection->alert_level !== 'level_3') {
            if ($blockDuration !== null) {
                $detection->block($blockDuration, 'level_2');
            }
            Event::dispatch(new AttackerLevel2Event($detection));
        }
    }

    /**
     * Trigger a level 3 alert.
     */
    protected function triggerLevel3Alert(AttackerDetection $detection, ?int $blockDuration): void
    {
        if ($detection->alert_level !== 'level_3') {
            // Level 3 can have null duration (permanent block)
            if ($blockDuration !== null) {
                $detection->block($blockDuration, 'level_3');
            } else {
                // Permanent block: set a very high duration (100 years)
                $detection->block(52560000, 'level_3');
            }
            Event::dispatch(new AttackerLevel3Event($detection));
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
    public function getDetectionsByLevel(string $level): \Illuminate\Database\Eloquent\Collection
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
