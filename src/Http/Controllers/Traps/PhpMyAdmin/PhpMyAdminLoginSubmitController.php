<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\PhpMyAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class PhpMyAdminLoginSubmitController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'phpmyadmin';
    }

    public function __invoke(Request $request): SymfonyResponse
    {
        return $this->executeLoginTrap($request, 'pma_username', 'pma_password');
    }

    protected function respondLoginFailed(Request $request, string $username): Response
    {
        $path = config('not-today-honey.traps.phpmyadmin.path', '/phpmyadmin');

        return response()->view('not-today-honey::traps.phpmyadmin.login-error', [
            'version' => config('not-today-honey.traps.phpmyadmin.specific.pma_version', '5.2.1'),
            'server' => config('not-today-honey.traps.phpmyadmin.specific.server', 'localhost'),
            'action' => rtrim($path, '/').'/',
            'username' => $username,
        ]);
    }

    protected function respondFakeSuccess(Request $request): Response
    {
        return response()->view('not-today-honey::traps.phpmyadmin.dashboard', [
            'version' => config('not-today-honey.traps.phpmyadmin.specific.pma_version', '5.2.1'),
            'server' => config('not-today-honey.traps.phpmyadmin.specific.server', 'localhost'),
        ]);
    }
}
