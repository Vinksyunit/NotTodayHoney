<?php

namespace Vinksyunit\NotTodayHoney\Enums;

enum TrapBehavior: string
{
    case FORBIDDEN = '403';
    case ERROR = '500';
    case INFINITE_LOADING = 'infinite_loading';
    case FAKE_SUCCESS = 'fake_success';
}
