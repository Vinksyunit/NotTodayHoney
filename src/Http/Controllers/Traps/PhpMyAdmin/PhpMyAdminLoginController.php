<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\PhpMyAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class PhpMyAdminLoginController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'phpmyadmin';
    }

    public function __invoke(Request $request): SymfonyResponse
    {
        return $this->executeTrap($request);
    }

    protected function respondLoginPage(Request $request): Response
    {
        $version = config('not-today-honey.traps.phpmyadmin.specific.pma_version', '5.2.1');

        return response($this->getPhpMyAdminLoginFormHtml($version), 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function getPhpMyAdminLoginFormHtml(string $version): string
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
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 60px auto;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        .logo { text-align: center; margin-bottom: 20px; font-size: 22px; color: #456798; font-weight: bold; }
        label { display: block; margin-bottom: 4px; font-size: 13px; color: #333; }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #aaa;
            border-radius: 3px;
            margin-bottom: 14px;
            font-size: 13px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background: #456798;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">phpMyAdmin</div>
    <form method="post" action="index.php" name="login_form">
        <p>
            <label for="input_username">Username:</label>
            <input type="text" name="pma_username" id="input_username" value="" autocomplete="username">
        </p>
        <p>
            <label for="input_password">Password:</label>
            <input type="password" name="pma_password" id="input_password" autocomplete="current-password">
        </p>
        <p>
            <input type="submit" value="Log in" id="input_go">
        </p>
        <input type="hidden" name="server" value="1">
    </form>
</div>
<!-- phpMyAdmin {$version} -->
</body>
</html>
HTML;
    }
}
