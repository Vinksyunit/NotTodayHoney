<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

trait HasHttpFingerprint
{
    protected function applyFingerprint(SymfonyResponse $response): SymfonyResponse
    {
        $trapName = $this->getTrapName();
        $enabled = config("not-today-honey.traps.{$trapName}.specific.fingerprint.enabled", false);

        if (! $enabled) {
            return $response;
        }

        return match ($trapName) {
            'wordpress' => $this->applyWordPressFingerprint($response),
            'phpmyadmin' => $this->applyPhpMyAdminFingerprint($response),
            default => $response,
        };
    }

    private function applyWordPressFingerprint(SymfonyResponse $response): SymfonyResponse
    {
        $host = request()->getSchemeAndHttpHost();
        $response->headers->set('Link', "<{$host}/wp-json/>; rel=\"https://api.w.org/\"");

        $phpVersion = config('not-today-honey.traps.wordpress.specific.fingerprint.php_version', '8.1.0');
        $response->headers->set('X-Powered-By', "PHP/{$phpVersion}");

        return $response;
    }

    private function applyPhpMyAdminFingerprint(SymfonyResponse $response): SymfonyResponse
    {
        $pmaVersion = config('not-today-honey.traps.phpmyadmin.specific.pma_version', '5.2.1');
        $lang = config('not-today-honey.traps.phpmyadmin.specific.fingerprint.lang', 'en');
        $cookieVer = (string) explode('.', $pmaVersion)[0];

        $response->headers->setCookie(new Cookie('phpMyAdmin', (string) Str::uuid(), httpOnly: true));
        $response->headers->setCookie(new Cookie('pma_lang', $lang, httpOnly: false));
        $response->headers->setCookie(new Cookie('pmaCookieVer', $cookieVer, httpOnly: true));

        return $response;
    }
}
