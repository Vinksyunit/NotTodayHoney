<?php

namespace Vinksyunit\NotTodayHoney\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vinksyunit\NotTodayHoney\NotTodayHoney
 */
class NotTodayHoney extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Vinksyunit\NotTodayHoney\NotTodayHoney::class;
    }
}
