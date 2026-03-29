<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Http\Controllers\Traps\WordPress;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Vinksyunit\NotTodayHoney\Http\Controllers\Traps\Concerns\HandlesTrapBehavior;

class WordPressLoginController
{
    use HandlesTrapBehavior;

    protected function getTrapName(): string
    {
        return 'wordpress';
    }

    public function __invoke(Request $request): SymfonyResponse
    {
        return $this->executeTrap($request);
    }

    protected function respondLoginPage(Request $request): Response
    {
        $version = config('not-today-honey.traps.wordpress.specific.version', '6.4.2');
        $path = config('not-today-honey.traps.wordpress.path', '/wp-admin');

        return response($this->getWordPressLoginFormHtml($version, $path), 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function getWordPressLoginFormHtml(string $version, string $path): string
    {
        $action = rtrim($path, '/').'/wp-login.php';

        return <<<HTML
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width">
    <meta name="robots" content="max-image-preview:large, noindex, noarchive">
    <title>Log In &lsaquo; WordPress &mdash; WordPress</title>
    <style type="text/css">
        html { background: #f1f1f1; }
        body {
            background: #fff;
            border: 1px solid #c3c4c7;
            color: #3c434a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            margin: 2em auto;
            padding: 1em 2em;
            max-width: 700px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
        }
        #login { width: 320px; margin: 100px auto; padding: 20px 0; }
        .login label { display: block; margin-bottom: 4px; font-weight: 600; }
        .login input[type="text"],
        .login input[type="password"] {
            display: block;
            width: 100%;
            padding: 8px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .login input[type="submit"] {
            background: #2271b1;
            border: none;
            border-radius: 3px;
            color: #fff;
            cursor: pointer;
            font-size: 13px;
            padding: 0 10px;
            height: 30px;
        }
    </style>
</head>
<body class="login wp-core-ui">
<div id="login">
    <h1><a href="https://wordpress.org/">WordPress</a></h1>
    <form name="loginform" id="loginform" action="{$action}" method="post">
        <p>
            <label for="user_login">Username or Email Address</label>
            <input type="text" name="log" id="user_login" value="" size="20" autocapitalize="none" autocomplete="username">
        </p>
        <p>
            <label for="user_pass">Password</label>
            <input type="password" name="pwd" id="user_pass" size="20" autocomplete="current-password">
        </p>
        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" value="Log In">
            <input type="hidden" name="redirect_to" value="/wp-admin/">
        </p>
    </form>
</div>
<!-- WordPress {$version} -->
</body>
</html>
HTML;
    }
}
