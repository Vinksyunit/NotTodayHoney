<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isBlocked(string $ip)
 * @method static \Illuminate\Database\Eloquent\Collection getBlockedIps()
 * @method static void unblock(string $ip)
 * @method static \Vinksyunit\NotTodayHoney\Models\AttackerDetection|null getDetection(string $ip)
 * @method static \Illuminate\Database\Eloquent\Collection getDetectionsByLevel(\Vinksyunit\NotTodayHoney\Enums\AlertLevel $level)
 *
 * @see \Vinksyunit\NotTodayHoney\NotTodayHoney
 */
class NotTodayHoney extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Vinksyunit\NotTodayHoney\NotTodayHoney::class;
    }
}
