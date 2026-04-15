<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class WordPressLoginSubmitController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'wordpress';
    }

    public function __invoke(Request $request): SymfonyResponse
    {
        return $this->executeLoginTrap($request, 'log', 'pwd');
    }

    protected function respondLoginFailed(Request $request, string $username): Response
    {
        $path = config('not-today-honey.traps.wordpress.path', '/wp-admin');

        return response()->view('not-today-honey::traps.wordpress.login-error', [
            'version' => config('not-today-honey.traps.wordpress.specific.version', '6.4.2'),
            'siteName' => config('not-today-honey.traps.wordpress.specific.site_name', 'WordPress'),
            'logoUrl' => config('not-today-honey.traps.wordpress.specific.logo_url'),
            'action' => rtrim($path, '/').'/wp-login.php',
            'username' => $username,
        ]);
    }

    protected function respondFakeSuccess(Request $request): Response
    {
        $path = config('not-today-honey.traps.wordpress.path', '/wp-admin');

        return response()->view('not-today-honey::traps.wordpress.dashboard', [
            'siteName' => config('not-today-honey.traps.wordpress.specific.site_name', 'WordPress'),
            'path' => rtrim($path, '/'),
        ]);
    }
}
