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
        $version = config('not-today-honey.traps.phpmyadmin.specific.pma_version', '5.2.1');

        $html = $this->getPhpMyAdminLoginErrorHtml($version);

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function getPhpMyAdminLoginErrorHtml(string $version): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>phpMyAdmin</title>
    <style>
        :root {
            --pma-blue: #456798;
            --pma-dark: #333;
            --error-bg: #ffcccc;
            --error-border: #cc0000;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 120px;
        }
        h1 {
            color: var(--pma-dark);
            font-size: 24px;
            margin: 0 0 20px 0;
            text-align: center;
        }
        .error {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            color: #660000;
        }
        .error-icon {
            font-weight: bold;
            margin-right: 5px;
        }
        .version {
            text-align: center;
            color: #888;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <svg width="120" height="40" viewBox="0 0 120 40">
                <text x="0" y="28" font-family="Arial" font-size="20" font-weight="bold" fill="#456798">phpMyAdmin</text>
            </svg>
        </div>
        <div class="error">
            <span class="error-icon">#1045</span>
            Cannot log in to the MySQL server
        </div>
        <p style="color: #666; font-size: 14px; text-align: center;">
            Access denied for user. Please check your credentials and try again.
        </p>
        <div class="version">phpMyAdmin {$version}</div>
    </div>
</body>
</html>
HTML;
    }
}
