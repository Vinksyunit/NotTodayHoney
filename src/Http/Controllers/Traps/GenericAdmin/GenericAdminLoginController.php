<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\GenericAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class GenericAdminLoginController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'generic_admin';
    }

    public function __invoke(Request $request): SymfonyResponse
    {
        return $this->executeTrap($request);
    }

    protected function respondLoginPage(Request $request): Response
    {
        $path = config('not-today-honey.traps.generic_admin.path', '/admin');

        return response()->view('not-today-honey::traps.generic-admin.login', [
            'title' => config('not-today-honey.traps.generic_admin.specific.title', 'Control Panel'),
            'action' => rtrim($path, '/').'/login',
        ]);
    }
}
