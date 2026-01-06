<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\PhpMyAdmin;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class PhpMyAdminLoginSubmitController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'phpmyadmin';
    }

    protected function getAlertLevel(): AlertLevel
    {
        return AlertLevel::INTRUSION_ATTEMPT;
    }

    public function __invoke(Request $request): Response
    {
        return $this->executeTrap($request);
    }
}
