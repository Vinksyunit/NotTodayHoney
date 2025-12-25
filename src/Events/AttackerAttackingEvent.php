<?php

namespace Vinksyunit\NotTodayHoney\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vinksyunit\NotTodayHoney\Concerns\HasAttackerAlertMethods;
use Vinksyunit\NotTodayHoney\Contracts\AttackerAlertEvent;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;

class AttackerAttackingEvent implements AttackerAlertEvent
{
    use Dispatchable, HasAttackerAlertMethods, SerializesModels;

    /**
     * The alert level for this event.
     */
    public const ALERT_LEVEL = AlertLevel::ATTACKING;
}
