<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Concerns;

use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;

trait HasAttackerAlertMethods
{
    /**
     * The attacker detection model.
     */
    public AttackerDetection $detection;

    /**
     * Create a new event instance.
     */
    public function __construct(AttackerDetection $detection)
    {
        $this->detection = $detection;
    }

    /**
     * Get the IP address of the attacker.
     */
    public function getIp(): string
    {
        return $this->detection->ip;
    }

    /**
     * Get the attempt count.
     */
    public function getAttemptCount(): int
    {
        return $this->detection->attempt_count ?? 0;
    }

    /**
     * Get the alert level.
     */
    public function getAlertLevel(): AlertLevel
    {
        return static::ALERT_LEVEL;
    }

    /**
     * Get the detection model.
     */
    public function getDetection(): AttackerDetection
    {
        return $this->detection;
    }
}
