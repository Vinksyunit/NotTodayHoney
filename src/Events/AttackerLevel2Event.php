<?php

namespace Vinksyunit\NotTodayHoney\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;

class AttackerLevel2Event
{
    use Dispatchable, SerializesModels;

    /**
     * The alert level for this event.
     */
    public const ALERT_LEVEL = 'level_2';

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
        return $this->detection->attempt_count;
    }

    /**
     * Get the alert level.
     */
    public function getAlertLevel(): string
    {
        return self::ALERT_LEVEL;
    }
}
