<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width">
    <meta name="robots" content="max-image-preview:large, noindex, noarchive">
    <title>Log In &lsaquo; {{ $siteName }} &mdash; WordPress</title>
    <style type="text/css">
        html { background: #f0f0f1; }
        body.login {
            background: transparent;
            color: #3c434a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 13px;
            font-weight: 400;
            margin: 0;
            padding: 0;
        }
        * { box-sizing: border-box; }
        a { color: #2271b1; text-decoration: none; }
        a:hover { color: #135e96; }
        #login { width: 320px; padding: 8% 0 0; margin: auto; }
        .login h1 a { display: block; width: 84px; height: 84px; margin: 0 auto 25px; }
        .login #login_error {
            background: #fff;
            border-left: 4px solid #d63638;
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            margin-bottom: 20px;
            padding: 12px;
            word-wrap: break-word;
        }
        .login #login_error strong { color: #d63638; }
        #nav, #backtoblog { font-size: 13px; margin: 0; padding: 0; text-align: center; }
        #nav a, #backtoblog a { color: #50575e; font-size: 0.85em; }
        #nav a:hover, #backtoblog a:hover { color: #135e96; }
        #nav { margin-top: 20px; }
        #backtoblog { margin-top: 10px; }
    </style>
</head>
<body class="login wp-core-ui">
<div id="login">
    <h1>
        <a href="https://wordpress.org/" title="Powered by WordPress" tabindex="-1">
            @include('not-today-honey::traps.wordpress.partials.logo')
        </a>
    </h1>
    <div id="login_error">
        <strong>Error:</strong> The username <strong>{{ $username }}</strong> is not registered on this site. If you are unsure of your username, try your email address instead.
    </div>
    <p id="nav">
        <a href="{{ $action }}?action=lostpassword">Lost your password?</a>
    </p>
    <p id="backtoblog">
        <a href="/">&larr; Go to {{ $siteName }}</a>
    </p>
</div>
<!-- WordPress {{ $version }} -->
</body>
</html>
