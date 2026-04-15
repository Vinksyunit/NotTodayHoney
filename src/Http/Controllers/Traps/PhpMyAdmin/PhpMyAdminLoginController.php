<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\PhpMyAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HasHttpFingerprint;

class PhpMyAdminLoginController
{
    use HandlesTrapBehavior;
    use HasHttpFingerprint;

    protected function getTrapName(): string
    {
        return 'phpmyadmin';
    }

    public function __invoke(Request $request): SymfonyResponse
    {
        return $this->applyFingerprint($request, $this->executeTrap($request));
    }

    protected function respondLoginPage(Request $request): Response
    {
        $path = config('not-today-honey.traps.phpmyadmin.path', '/phpmyadmin');

        return response()->view('not-today-honey::traps.phpmyadmin.login', [
            'version' => config('not-today-honey.traps.phpmyadmin.specific.pma_version', '5.2.1'),
            'server' => config('not-today-honey.traps.phpmyadmin.specific.server', 'localhost'),
            'action' => rtrim($path, '/').'/',
        ]);
    }
}
