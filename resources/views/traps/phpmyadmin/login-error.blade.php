<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>phpMyAdmin</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            background: #f3f3f4;
            color: #333;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
        }
        a { color: #456798; text-decoration: none; }
        a:hover { text-decoration: underline; }

        #pma_header {
            background: #fff;
            border-bottom: 1px solid #ddd;
            padding: 8px 20px;
        }
        #pma_header .logo { color: #456798; font-size: 22px; font-weight: bold; letter-spacing: -0.5px; }
        #pma_header .logo span { color: #f90; }

        #login_form_container { display: flex; justify-content: center; padding: 40px 20px; }
        #login_form {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,.08);
            padding: 30px;
            width: 340px;
        }
        #login_form .form-title {
            border-bottom: 1px solid #eee;
            color: #456798;
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 20px;
            padding-bottom: 12px;
        }
        .login-server-info {
            align-items: center;
            background: #f8f8f8;
            border: 1px solid #e8e8e8;
            border-radius: 3px;
            color: #666;
            display: flex;
            font-size: 12px;
            gap: 6px;
            margin-bottom: 20px;
            padding: 8px 10px;
        }
        .login-server-info::before { color: #456798; content: "⬡"; font-size: 16px; }
        .error-message {
            background: #fdf2f2;
            border: 1px solid #e88;
            border-left: 4px solid #c00;
            border-radius: 3px;
            color: #900;
            font-size: 13px;
            margin-bottom: 20px;
            padding: 10px 12px;
        }
        .error-message .error-code { font-family: monospace; font-weight: 600; }
        label { color: #555; display: block; font-size: 13px; margin-bottom: 4px; }
        input[type="text"],
        input[type="password"],
        select {
            background: #fff;
            border: 1px solid #aaa;
            border-radius: 3px;
            color: #333;
            font-family: inherit;
            font-size: 13px;
            margin-bottom: 14px;
            padding: 7px 10px;
            width: 100%;
        }
        input[type="text"]:focus,
        input[type="password"]:focus { border-color: #456798; outline: none; box-shadow: 0 0 0 2px rgba(69,103,152,.15); }
        .login-lang { border-top: 1px solid #eee; margin: 16px 0; padding-top: 16px; }
        input[type="submit"] {
            background: #456798;
            border: none;
            border-radius: 3px;
            color: #fff;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            padding: 9px 20px;
            width: 100%;
        }
        input[type="submit"]:hover { background: #365480; }

        #pma_footer { color: #999; font-size: 11px; padding: 16px 20px; text-align: center; }
    </style>
</head>
<body>
<div id="pma_header">
    <span class="logo">php<span>My</span>Admin</span>
</div>

<div id="login_form_container">
    <div id="login_form">
        <p class="form-title">Log in</p>
        <div class="error-message">
            <span class="error-code">#1045</span> &mdash; Cannot log in to the MySQL server.<br>
            <small>Access denied for user '{{ $username }}'@'{{ $server }}' (using password: YES)</small>
        </div>
        <div class="login-server-info">MySQL &mdash; {{ $server }}</div>
        <form method="post" action="{{ $action }}" name="login_form">
            <label for="input_username">Username:</label>
            <input type="text" name="pma_username" id="input_username" value="{{ $username }}" autocomplete="username">

            <label for="input_password">Password:</label>
            <input type="password" name="pma_password" id="input_password" autocomplete="current-password">

            <div class="login-lang">
                <label for="sel_lang">Language:</label>
                <select name="lang" id="sel_lang">
                    <option value="en" selected>English</option>
                    <option value="fr">Français</option>
                    <option value="de">Deutsch</option>
                    <option value="es">Español</option>
                    <option value="zh">中文</option>
                </select>
            </div>

            <input type="hidden" name="server" value="1">
            <input type="submit" value="Log in" id="input_go">
        </form>
    </div>
</div>

<div id="pma_footer">
    phpMyAdmin {{ $version }} &mdash; <a href="https://www.phpmyadmin.net" target="_blank" rel="noopener">www.phpmyadmin.net</a>
</div>
</body>
</html>
