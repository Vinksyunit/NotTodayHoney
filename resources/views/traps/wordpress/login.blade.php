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
        .login form {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
            margin-top: 20px;
            overflow: hidden;
            padding: 26px 24px 46px;
        }
        .login label { display: block; font-size: 14px; font-weight: 400; margin-bottom: 4px; }
        .login input[type="text"],
        .login input[type="password"] {
            background-color: #fff;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            color: #2c3338;
            display: block;
            font-size: 24px;
            margin: 0;
            padding: 3px 10px;
            width: 100%;
        }
        .login input[type="text"]:focus,
        .login input[type="password"]:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: 2px solid transparent;
        }
        .login p { margin: 0 0 20px; }
        .user-pass-wrap { margin-bottom: 20px; }
        .wp-pwd { display: flex; align-items: stretch; }
        .wp-pwd input[type="password"] { border-radius: 4px 0 0 4px; flex: 1; }
        .wp-hide-pw {
            align-items: center;
            background: #fff;
            border: 1px solid #8c8f94;
            border-left: none;
            border-radius: 0 4px 4px 0;
            color: #50575e;
            cursor: pointer;
            display: flex;
            justify-content: center;
            min-height: 45px;
            padding: 0 10px;
        }
        .wp-hide-pw:hover { border-color: #646970; color: #2c3338; }
        .login p.forgetmenot { float: left; margin-bottom: 0; }
        .login p.submit { float: right; margin-bottom: 0; padding: 0; }
        .login input[type="submit"] {
            background: #2271b1;
            border: 1px solid #2271b1;
            border-radius: 3px;
            color: #fff;
            cursor: pointer;
            font-family: inherit;
            font-size: 13px;
            line-height: 2.15384615;
            min-height: 30px;
            padding: 0 10px;
        }
        .login input[type="submit"]:hover { background: #135e96; border-color: #135e96; }
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
    <form name="loginform" id="loginform" action="{{ $action }}" method="post">
        <p>
            <label for="user_login">Username or Email Address</label>
            <input type="text" name="log" id="user_login" value="" size="20" autocapitalize="none" autocomplete="username" required>
        </p>
        <div class="user-pass-wrap">
            <label for="user_pass">Password</label>
            <div class="wp-pwd">
                <input type="password" name="pwd" id="user_pass" value="" size="20" autocomplete="current-password" spellcheck="false" required>
                <button type="button" class="wp-hide-pw" aria-label="Show password">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                    </svg>
                </button>
            </div>
        </div>
        <p class="forgetmenot">
            <label for="rememberme">
                <input name="rememberme" type="checkbox" id="rememberme" value="forever"> Remember Me
            </label>
        </p>
        <p class="submit">
            <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Log In">
            <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            <input type="hidden" name="testcookie" value="1">
        </p>
    </form>
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
