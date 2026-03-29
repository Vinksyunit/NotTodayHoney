# Trap Behavior Login Success Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix `TrapBehavior` so it only fires on fake login success (known password matched), rename the config key to `login_success_behavior`, and make GET requests always render the realistic login page.

**Architecture:** Three changes in concert — config key renamed, trait routing fixed (`executeTrap` calls new `respondLoginPage()`, `executeLoginTrap` applies behavior only on password match), each GET controller overrides `respondLoginPage()` with realistic HTML.

**Tech Stack:** PHP 8.1+, Laravel, Pest

---

## File Map

| File | Change |
|---|---|
| `config/not-today-honey.php` | Rename `behavior` → `login_success_behavior`, update env vars, change default |
| `src/Http/Controllers/Traps/Concerns/HandlesTrapBehavior.php` | Fix `executeTrap()`, add `respondLoginPage()`, fix config key read in `executeLoginTrap()` |
| `src/Http/Controllers/Traps/WordPress/WordPressLoginController.php` | Override `respondLoginPage()` with WP login form HTML |
| `src/Http/Controllers/Traps/PhpMyAdmin/PhpMyAdminLoginController.php` | Override `respondLoginPage()` with phpMyAdmin login form HTML |
| `src/Http/Controllers/Traps/GenericAdmin/GenericAdminLoginController.php` | Override `respondLoginPage()` with generic admin login form HTML |
| `tests/Feature/Traps/WordPressTrapTest.php` | Replace GET 403 test with GET login form test; update config key in known-password test |
| `tests/Feature/Traps/PhpMyAdminTrapTest.php` | Add GET login form content test |
| `tests/Feature/Traps/GenericAdminTrapTest.php` | Add GET login form content test |

---

## Task 1: Write failing tests

**Files:**
- Modify: `tests/Feature/Traps/WordPressTrapTest.php`
- Modify: `tests/Feature/Traps/PhpMyAdminTrapTest.php`
- Modify: `tests/Feature/Traps/GenericAdminTrapTest.php`

- [ ] **Step 1: Replace the WordPress GET 403 test and update the known-password behavior test**

In `tests/Feature/Traps/WordPressTrapTest.php`, replace:

```php
it('GET wp-login.php returns 403 when behavior is FORBIDDEN', function () {
    $this->get('/wp-login.php')->assertStatus(403);
});
```

with:

```php
it('GET wp-login.php returns WordPress login form', function () {
    $response = $this->get('/wp-login.php');

    $response->assertStatus(200);
    $response->assertSee('WordPress', false);
    $response->assertSee('Log In', false);
    $response->assertSee('name="log"', false);
    $response->assertSee('name="pwd"', false);
});
```

And update the known-password behavior test (replace `'behavior'` with `'login_success_behavior'`):

```php
it('POST wp-login.php with known password responds with configured login_success_behavior', function () {
    config()->set('not-today-honey.traps.wordpress.login_success_behavior', TrapBehavior::FORBIDDEN);

    $this->post('/wp-login.php', ['log' => 'admin', 'pwd' => 'password'])
        ->assertStatus(403);
});
```

- [ ] **Step 2: Add phpMyAdmin GET login form test**

In `tests/Feature/Traps/PhpMyAdminTrapTest.php`, add after the existing GET PROBING test:

```php
it('GET /phpmyadmin returns phpMyAdmin login form', function () {
    $response = $this->get('/phpmyadmin/');

    $response->assertStatus(200);
    $response->assertSee('phpMyAdmin', false);
    $response->assertSee('name="pma_username"', false);
    $response->assertSee('name="pma_password"', false);
});
```

- [ ] **Step 3: Add GenericAdmin GET login form test**

In `tests/Feature/Traps/GenericAdminTrapTest.php`, add after the existing GET PROBING test:

```php
it('GET /admin/login returns generic admin login form', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
    $response->assertSee('Control Panel', false);
    $response->assertSee('name="username"', false);
    $response->assertSee('name="password"', false);
});
```

- [ ] **Step 4: Run tests to confirm the new tests fail**

```bash
composer test
```

Expected: New GET login form tests FAIL (currently GET returns 403 from FORBIDDEN default); `login_success_behavior` test FAILS (key not found yet).

---

## Task 2: Rename config key and update defaults

**Files:**
- Modify: `config/not-today-honey.php`

- [ ] **Step 1: Replace the three trap `behavior` entries with `login_success_behavior`**

In `config/not-today-honey.php`, replace the `'traps'` section entirely:

```php
    /*
    |--------------------------------------------------------------------------
    | Honeypot Traps
    |--------------------------------------------------------------------------
    |
    | Comportements disponibles après un faux login réussi :
    | '403'             -> Accès interdit.
    | '500'             -> Simule une erreur serveur.
    | 'infinite_loading'-> Fait ramer la requête jusqu'au timeout.
    | 'fake_success'    -> Simule un dashboard vide (comportement par défaut).
    |
    */
    'traps' => [

        'wordpress' => [
            'enabled' => env('NOT_TODAY_HONEY_WP_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_WP_PATH', '/wp-admin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_WP_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
            'specific' => [
                'version' => env('NOT_TODAY_HONEY_WP_VERSION', '6.4.2'),
            ],
        ],

        'phpmyadmin' => [
            'enabled' => env('NOT_TODAY_HONEY_PMA_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_PMA_PATH', '/phpmyadmin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_PMA_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
            'specific' => [
                'pma_version' => env('NOT_TODAY_HONEY_PMA_VERSION', '5.2.1'),
            ],
        ],

        'generic_admin' => [
            'enabled' => env('NOT_TODAY_HONEY_GENERIC_ENABLED', true),
            'path' => env('NOT_TODAY_HONEY_GENERIC_PATH', '/admin'),
            'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_GENERIC_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
            'specific' => [
                'title' => env('NOT_TODAY_HONEY_GENERIC_TITLE', 'Control Panel'),
            ],
        ],

    ],
```

- [ ] **Step 2: Run tests**

```bash
composer test
```

Expected: GET login form tests still FAIL (trait not updated yet). The `login_success_behavior` behavior test now finds the config key but the trait still reads `behavior` → it will 500 or return wrong response — that's fine, we fix it next.

- [ ] **Step 3: Commit**

```bash
git add config/not-today-honey.php
git commit -m "config: rename behavior to login_success_behavior, default to fake_success"
```

---

## Task 3: Fix trait routing

**Files:**
- Modify: `src/Http/Controllers/Traps/Concerns/HandlesTrapBehavior.php`

- [ ] **Step 1: Replace `executeTrap()` and update `executeLoginTrap()`, add `respondLoginPage()`**

Replace the `executeTrap()` method (lines 38–48) with:

```php
    /**
     * Execute the trap behavior and log the attempt.
     */
    protected function executeTrap(Request $request): SymfonyResponse
    {
        $detection = $this->recordDetection($request);

        $this->logTrapAttempt($request, $detection->id);

        return $this->respondLoginPage($request);
    }
```

Replace the config read inside `executeLoginTrap()` (lines 80–84) with:

```php
        if ($credentialCheck['password_matched']) {
            $trapConfig = config("not-today-honey.traps.{$this->getTrapName()}");

            return $this->respondWithBehavior($trapConfig['login_success_behavior'], $request);
        }
```

Add the base `respondLoginPage()` method after `respondLoginFailed()` (after line 151):

```php
    /**
     * Return the realistic login page for this trap.
     * Override in specific GET controllers.
     */
    protected function respondLoginPage(Request $request): Response
    {
        return response('', 200);
    }
```

- [ ] **Step 2: Run tests**

```bash
composer test
```

Expected: GET PROBING detection tests still PASS. GET login form tests FAIL (base returns blank 200, no HTML yet). POST wrong-credential tests PASS. POST known-password tests PASS.

- [ ] **Step 3: Commit**

```bash
git add src/Http/Controllers/Traps/Concerns/HandlesTrapBehavior.php
git commit -m "feat: fix trap behavior routing — GET renders login page, POST success applies login_success_behavior"
```

---

## Task 4: WordPress GET controller — login form

**Files:**
- Modify: `src/Http/Controllers/Traps/WordPress/WordPressLoginController.php`

- [ ] **Step 1: Run the failing GET test to confirm the target**

```bash
composer test -- --filter "GET wp-login.php returns WordPress login form"
```

Expected: FAIL — response body is empty, assertions on `Log In` and form fields fail.

- [ ] **Step 2: Override `respondLoginPage()` in `WordPressLoginController`**

Replace the entire file with:

```php
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
    <form name="loginform" id="loginform" action="/wp-login.php" method="post">
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
```

- [ ] **Step 3: Run the WordPress tests**

```bash
composer test -- --filter "WordPressTrapTest"
```

Expected: All 6 tests PASS.

- [ ] **Step 4: Commit**

```bash
git add src/Http/Controllers/Traps/WordPress/WordPressLoginController.php tests/Feature/Traps/WordPressTrapTest.php
git commit -m "feat: WordPress GET trap renders realistic login form"
```

---

## Task 5: phpMyAdmin GET controller — login form

**Files:**
- Modify: `src/Http/Controllers/Traps/PhpMyAdmin/PhpMyAdminLoginController.php`

- [ ] **Step 1: Run the failing GET test**

```bash
composer test -- --filter "GET /phpmyadmin returns phpMyAdmin login form"
```

Expected: FAIL — empty body.

- [ ] **Step 2: Override `respondLoginPage()` in `PhpMyAdminLoginController`**

Replace the entire file with:

```php
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
```

- [ ] **Step 3: Run the phpMyAdmin tests**

```bash
composer test -- --filter "PhpMyAdminTrapTest"
```

Expected: All tests PASS.

- [ ] **Step 4: Commit**

```bash
git add src/Http/Controllers/Traps/PhpMyAdmin/PhpMyAdminLoginController.php tests/Feature/Traps/PhpMyAdminTrapTest.php
git commit -m "feat: phpMyAdmin GET trap renders realistic login form"
```

---

## Task 6: GenericAdmin GET controller — login form

**Files:**
- Modify: `src/Http/Controllers/Traps/GenericAdmin/GenericAdminLoginController.php`

- [ ] **Step 1: Run the failing GET test**

```bash
composer test -- --filter "GET /admin/login returns generic admin login form"
```

Expected: FAIL — empty body.

- [ ] **Step 2: Override `respondLoginPage()` in `GenericAdminLoginController`**

Replace the entire file with:

```php
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
```

- [ ] **Step 3: Run the GenericAdmin tests**

```bash
composer test -- --filter "GenericAdminTrapTest"
```

Expected: All tests PASS.

- [ ] **Step 4: Run the full test suite**

```bash
composer test
```

Expected: All tests PASS.

- [ ] **Step 5: Commit**

```bash
git add src/Http/Controllers/Traps/GenericAdmin/GenericAdminLoginController.php tests/Feature/Traps/GenericAdminTrapTest.php
git commit -m "feat: GenericAdmin GET trap renders realistic login form"
```
