<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\GenericAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class GenericAdminLoginSubmitController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'generic_admin';
    }

    public function __invoke(Request $request): SymfonyResponse
    {
        return $this->executeLoginTrap($request, 'username', 'password');
    }

    protected function respondLoginFailed(Request $request, string $username): Response
    {
        $path = config('not-today-honey.traps.generic_admin.path', '/admin');

        return response()->view('not-today-honey::traps.generic-admin.login-error', [
            'title' => config('not-today-honey.traps.generic_admin.specific.title', 'Control Panel'),
            'action' => rtrim($path, '/').'/login',
            'username' => $username,
        ]);
    }

    protected function respondFakeSuccess(Request $request): Response
    {
        $path = config('not-today-honey.traps.generic_admin.path', '/admin');

        return response()->view('not-today-honey::traps.generic-admin.dashboard', [
            'title' => config('not-today-honey.traps.generic_admin.specific.title', 'Control Panel'),
            'loginPath' => rtrim($path, '/').'/login',
        ]);
    }
}
