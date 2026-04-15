<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TrapCampaignDetectedEvent
{
    use Dispatchable;

    public function __construct(
        public readonly int $maxHits,
        public readonly int $decayMinutes,
    ) {}
}
