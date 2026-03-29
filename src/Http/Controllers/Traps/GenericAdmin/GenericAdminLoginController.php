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
        $title = config('not-today-honey.traps.generic_admin.specific.title', 'Control Panel');

        return response($this->getGenericAdminLoginFormHtml($title), 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function getGenericAdminLoginFormHtml(string $title): string
    {
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>{$escapedTitle} - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 32px;
            width: 360px;
            box-shadow: 0 4px 24px rgba(0,0,0,.15);
        }
        h2 { text-align: center; margin-bottom: 24px; color: #333; font-size: 20px; }
        label { display: block; margin-bottom: 4px; font-size: 13px; color: #555; }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #667eea;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }
        .error { color: #c00; font-size: 13px; margin-bottom: 12px; display: none; }
    </style>
</head>
<body>
<div class="card">
    <h2>{$escapedTitle}</h2>
    <form method="post" action="">
        <div class="error" id="login-error">Invalid credentials. Please try again.</div>
        <p>
            <label for="username">Username</label>
            <input type="text" name="username" id="username" autocomplete="username" required>
        </p>
        <p>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" autocomplete="current-password" required>
        </p>
        <p>
            <input type="submit" value="Sign In">
        </p>
    </form>
</div>
</body>
</html>
HTML;
    }
}
