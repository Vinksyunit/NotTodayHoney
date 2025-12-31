<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Enums;

enum AlertLevel: string
{
    case PROBING = 'probing';
    case INTRUSION_ATTEMPT = 'intrusion_attempt';
    case ATTACKING = 'attacking';
}
