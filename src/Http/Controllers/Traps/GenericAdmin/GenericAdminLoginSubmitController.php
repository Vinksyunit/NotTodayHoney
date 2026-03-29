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
        $title = config('not-today-honey.traps.generic_admin.specific.title', 'Control Panel');

        $html = $this->getGenericAdminLoginErrorHtml($title);

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function getGenericAdminLoginErrorHtml(string $title): string
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
            text-align: center;
        }
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            color: #c00;
            text-align: center;
        }
        .error-icon {
            font-size: 20px;
            margin-right: 8px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>{$escapedTitle}</h1>
        <div class="error-message">
            <span class="error-icon">⚠</span>
            Invalid username or password
        </div>
        <p style="color: #666; text-align: center; font-size: 14px;">
            Please check your credentials and try again.
        </p>
        <a href="login" class="back-link">← Back to login</a>
    </div>
</body>
</html>
HTML;
    }
}
