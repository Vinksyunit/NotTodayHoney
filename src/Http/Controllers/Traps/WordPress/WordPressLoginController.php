<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HasHttpFingerprint;

class WordPressLoginController
{
    use HandlesTrapBehavior;
    use HasHttpFingerprint;

    protected function getTrapName(): string
    {
        return 'wordpress';
    }

    public function __invoke(Request $request): SymfonyResponse
    {
        return $this->applyFingerprint($this->executeTrap($request));
    }

    protected function respondLoginPage(Request $request): Response
    {
        $path = config('not-today-honey.traps.wordpress.path', '/wp-admin');

        return response()->view('not-today-honey::traps.wordpress.login', [
            'version' => config('not-today-honey.traps.wordpress.specific.version', '6.4.2'),
            'siteName' => config('not-today-honey.traps.wordpress.specific.site_name', 'WordPress'),
            'logoUrl' => config('not-today-honey.traps.wordpress.specific.logo_url'),
            'action' => rtrim($path, '/').'/wp-login.php',
            'redirectTo' => rtrim($path, '/').'/',
        ]);
    }
}
