<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vinksyunit\NotTodayHoney\Concerns\HasAttackerAlertMethods;
use Vinksyunit\NotTodayHoney\Contracts\AttackerAlertEvent;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;

class AttackerProbingEvent implements AttackerAlertEvent
{
    use Dispatchable;
    use HasAttackerAlertMethods;
    use SerializesModels;

    /**
     * The alert level for this event.
     */
    public const ALERT_LEVEL = AlertLevel::PROBING;
}
