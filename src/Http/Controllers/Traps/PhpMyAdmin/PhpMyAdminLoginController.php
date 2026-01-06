<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\PhpMyAdmin;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class PhpMyAdminLoginController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'phpmyadmin';
    }

    public function __invoke(Request $request): Response
    {
        return $this->executeTrap($request);
    }
}
