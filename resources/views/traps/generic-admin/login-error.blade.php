<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title }} — Sign In</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
            padding: 40px 36px;
            width: 380px;
        }
        .card-title { color: #1a1a2e; font-size: 22px; font-weight: 700; margin: 0 0 6px; text-align: center; }
        .card-subtitle { color: #888; font-size: 13px; margin: 0 0 20px; text-align: center; }
        .alert-error {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-left: 4px solid #e53e3e;
            border-radius: 6px;
            color: #c53030;
            font-size: 13px;
            margin-bottom: 20px;
            padding: 10px 12px;
        }
        label { color: #444; display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; }
        input[type="text"],
        input[type="password"] {
            background: #f9f9fb;
            border: 1px solid #ddd;
            border-radius: 6px;
            color: #222;
            font-family: inherit;
            font-size: 14px;
            margin-bottom: 18px;
            padding: 10px 12px;
            width: 100%;
        }
        input[type="text"]:focus,
        input[type="password"]:focus { border-color: #667eea; outline: none; box-shadow: 0 0 0 3px rgba(102,126,234,.15); }
        input[type="submit"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            padding: 11px;
            width: 100%;
        }
        input[type="submit"]:hover { opacity: .92; }
        .card-footer { color: #aaa; font-size: 11px; margin-top: 24px; text-align: center; }
    </style>
</head>
<body>
<div class="card">
    <p class="card-title">{{ $title }}</p>
    <p class="card-subtitle">Sign in to your account</p>
    <div class="alert-error">Invalid username or password. Please try again.</div>
    <form method="post" action="{{ $action }}">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="{{ $username }}" autocomplete="username" required>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" autocomplete="current-password" required>

        <input type="submit" value="Sign In">
    </form>
    <p class="card-footer">Restricted area &mdash; authorised access only</p>
</div>
</body>
</html>
