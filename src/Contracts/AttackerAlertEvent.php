<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Contracts;

use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Models\AttackerDetection;

interface AttackerAlertEvent
{
    /**
     * Get the IP address of the attacker.
     */
    public function getIp(): string;

    /**
     * Get the attempt count.
     */
    public function getAttemptCount(): int;

    /**
     * Get the alert level.
     */
    public function getAlertLevel(): AlertLevel;

    /**
     * Get the detection model.
     */
    public function getDetection(): AttackerDetection;
}
