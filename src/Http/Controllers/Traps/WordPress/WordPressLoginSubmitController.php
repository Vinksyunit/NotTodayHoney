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
        $version = config('not-today-honey.traps.wordpress.specific.version', '6.4.2');

        $html = $this->getWordPressLoginErrorHtml($username, $version);

        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function getWordPressLoginErrorHtml(string $username, string $version): string
    {
        $escapedUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

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
            -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
        }
        #login {
            width: 320px;
            margin: 100px auto;
            padding: 20px 0;
        }
        .login h1 a {
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0MCA0MCIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIj48cGF0aCBmaWxsPSIjMzg1ODk4IiBkPSJNMjAgMEM4Ljk1NCAwIDAgOC45NTQgMCAyMHM4Ljk1NCAyMCAyMCAyMCAyMC04Ljk1NCAyMC0yMFMzMS4wNDYgMCAyMCAwem0xMC42IDE1LjZjLS4wMDEuMTk3LS4wMDcuMzkzLS4wMTguNTg4IDAgLjEtLjAxLjItLjAyLjMtLjAzLjQ5LS4wOC45OC0uMTUgMS40Ny0uMDIuMTQtLjA0LjI4LS4wNy40Mi0uMDcuMzc5LS4xNS43NS0uMjUgMS4xMi0uMDMuMTItLjA2LjI0LS4xLjM2LS4xMy40LS4yNy44LS40MyAxLjE5LS4wNC4xLS4wOC4yLS4xMi4zLS4xOS40My0uNC44NS0uNjMgMS4yNi0uMDQuMDctLjA3LjE0LS4xMS4yMS0uMjYuNDUtLjU0Ljg5LS44NSAxLjMxLS4wNS4wNy0uMS4xMy0uMTUuMi0uMzQuNDMtLjcuODUtMS4wOSAxLjI0bC0uMTguMThjLS40My40Mi0uODguODItMS4zNiAxLjE5LS4wNy4wNS0uMTQuMS0uMjEuMTUtLjQzLjMyLS44OC42Mi0xLjM1Ljg5LS4xLjA2LS4yLjExLS4zLjE3LS40My4yNS0uODcuNDgtMS4zMy42OWwtLjM2LjE2Yy0uNTIuMjMtMS4wNS40My0xLjYuNjFsLS4yMi4wN2MtLjUzLjE2LTEuMDcuMy0xLjYyLjQxbC0uMjkuMDZjLS41NC4xLTEuMDkuMTctMS42NS4yMmwtLjE1LjAxYy0uNTMuMDQtMS4wNi4wNi0xLjYuMDYtLjUzIDAtMS4wNi0uMDItMS41OC0uMDZsLS4xNS0uMDFjLS41Ni0uMDUtMS4xMS0uMTItMS42NS0uMjJsLS4yOS0uMDZjLS41NS0uMTEtMS4wOS0uMjUtMS42Mi0uNDFsLS4yMi0uMDdjLS41NS0uMTgtMS4wOC0uMzgtMS42LS42MWwtLjM2LS4xNmMtLjQ2LS4yMS0uOS0uNDQtMS4zMy0uNjktLjEtLjA2LS4yLS4xMS0uMy0uMTctLjQ3LS4yNy0uOTItLjU3LTEuMzUtLjg5LS4wNy0uMDUtLjE0LS4xLS4yMS0uMTUtLjQ4LS4zNy0uOTMtLjc3LTEuMzYtMS4xOWwtLjE4LS4xOGMtLjM5LS4zOS0uNzUtLjgxLTEuMDktMS4yNC0uMDUtLjA3LS4xLS4xMy0uMTUtLjItLjMxLS40Mi0uNTktLjg2LS44NS0xLjMxLS4wNC0uMDctLjA3LS4xNC0uMTEtLjIxLS4yMy0uNDEtLjQ0LS44My0uNjMtMS4yNi0uMDQtLjEtLjA4LS4yLS4xMi0uMy0uMTYtLjM5LS4zLS43OS0uNDMtMS4xOS0uMDQtLjEyLS4wNy0uMjQtLjEtLjM2LS4xLS4zNy0uMTgtLjc0MS0uMjUtMS4xMi0uMDMtLjE0LS4wNS0uMjgtLjA3LS40Mi0uMDctLjQ5LS4xMi0uOTgtLjE1LTEuNDctLjAxLS4xLS4wMi0uMi0uMDItLjMtLjAxMS0uMTk1LS4wMTctLjM5MS0uMDE4LS41ODggMC0uMTk3LjAwNy0uMzkzLjAxOC0uNTg4IDAtLjEuMDEtLjIuMDItLjMuMDMtLjQ5LjA4LS45OC4xNS0xLjQ3LjAyLS4xNC4wNC0uMjguMDctLjQyLjA3LS4zNzkuMTUtLjc1LjI1LTEuMTIuMDMtLjEyLjA2LS4yNC4xLS4zNi4xMy0uNC4yNy0uOC40My0xLjE5LjA0LS4xLjA4LS4yLjEyLS4zLjE5LS40My40LS44NS42My0xLjI2LjA0LS4wNy4wNy0uMTQuMTEtLjIxLjI2LS40NS41NC0uODkuODUtMS4zMS4wNS0uMDcuMS0uMTMuMTUtLjIuMzQtLjQzLjctLjg1IDEuMDktMS4yNGwuMTgtLjE4Yy40My0uNDIuODgtLjgyIDEuMzYtMS4xOS4wNy0uMDUuMTQtLjEuMjEtLjE1LjQzLS4zMi44OC0uNjIgMS4zNS0uODkuMS0uMDYuMi0uMTEuMy0uMTcuNDMtLjI1Ljg3LS40OCAxLjMzLS42OWwuMzYtLjE2Yy41Mi0uMjMgMS4wNS0uNDMgMS42LS42MWwuMjItLjA3Yy41My0uMTYgMS4wNy0uMyAxLjYyLS40MWwuMjktLjA2Yy41NC0uMSAxLjA5LS4xNyAxLjY1LS4yMmwuMTUtLjAxYy41My0uMDQgMS4wNi0uMDYgMS42LS4wNi41MyAwIDEuMDYuMDIgMS41OC4wNmwuMTUuMDFjLjU2LjA1IDEuMTEuMTIgMS42NS4yMmwuMjkuMDZjLjU1LjExIDEuMDkuMjUgMS42Mi40MWwuMjIuMDdjLjU1LjE4IDEuMDguMzggMS42LjYxbC4zNi4xNmMuNDYuMjEuOS40NCAxLjMzLjY5LjEuMDYuMi4xMS4zLjE3LjQ3LjI3LjkyLjU3IDEuMzUuODkuMDcuMDUuMTQuMS4yMS4xNS40OC4zNy45My43NyAxLjM2IDEuMTlsLjE4LjE4Yy4zOS4zOS43NS44MSAxLjA5IDEuMjQuMDUuMDcuMS4xMy4xNS4yLjMxLjQyLjU5Ljg2Ljg1IDEuMzEuMDQuMDcuMDcuMTQuMTEuMjEuMjMuNDEuNDQuODMuNjMgMS4yNi4wNC4xLjA4LjIuMTIuMy4xNi4zOS4zLjc5LjQzIDEuMTkuMDQuMTIuMDcuMjQuMS4zNi4xLjM3LjE4Ljc0MS4yNSAxLjEyLjAzLjE0LjA1LjI4LjA3LjQyLjA3LjQ5LjEyLjk4LjE1IDEuNDcuMDEuMS4wMi4yLjAyLjMuMDExLjE5NS4wMTcuMzkxLjAxOC41ODh6Ii8+PC9zdmc+');
            background-size: 84px;
            background-position: center top;
            background-repeat: no-repeat;
            color: #3c434a;
            height: 84px;
            font-size: 20px;
            font-weight: 400;
            line-height: 1.3;
            margin: 0 auto 25px;
            padding: 0;
            text-decoration: none;
            width: 84px;
            text-indent: -9999px;
            outline: 0;
            overflow: hidden;
            display: block;
        }
        .login #login_error {
            border-left: 4px solid #d63638;
            padding: 12px;
            margin-left: 0;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            word-wrap: break-word;
        }
        .login #login_error strong {
            color: #d63638;
        }
    </style>
</head>
<body class="login wp-core-ui">
    <div id="login">
        <h1><a href="https://wordpress.org/">WordPress</a></h1>
        <div id="login_error">
            <strong>Error:</strong> The username <strong>{$escapedUsername}</strong> is not registered on this site. If you are unsure of your username, try your email address instead.
        </div>
    </div>
    <!-- WordPress {$version} -->
</body>
</html>
HTML;
    }
}
