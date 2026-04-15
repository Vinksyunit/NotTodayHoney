# Protect Your Features

Once an attacker is detected, NotTodayHoney blocks their IP. This guide explains how to enforce that block across your application.

## How blocking works

When a detection threshold is reached and `mark_as_insecure` is `true`, a block record is written to the database with an expiry time. The `nottodayhoney.block` middleware checks this record on every request and returns a `403` if the IP is currently blocked.

Whitelisted IPs always pass through — even if they have an active detection record.

## Applying the middleware

**Globally** — blocks all routes for any blocked IP:

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotBlockMiddleware::class);
})
```

**Per route group** — protects only specific routes:

```php
Route::middleware('nottodayhoney.block')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/api/login', [AuthController::class, 'login']);
});
```

Apply at minimum to your real login routes and admin areas. Global application is recommended if your application does not serve public content to anonymous users.

## Managing blocked IPs

**Check status in the terminal:**

```bash
php artisan honey:status
```

**Unblock an IP:**

```bash
php artisan honey:unblock 1.2.3.4
```

**Programmatically via the facade:**

```php
use Vinksyunit\NotTodayHoney\Facades\NotTodayHoney;

// Check if an IP is blocked
NotTodayHoney::isBlocked('1.2.3.4'); // bool

// List all blocked IPs
NotTodayHoney::getBlockedIps(); // Collection<AttackerDetection>

// Unblock
NotTodayHoney::unblock('1.2.3.4');
```

See [Artisan Commands](/commands) and [Events & Middleware → Facade API](/events-middleware#facade-api) for the full reference.

## Timing normalization

Every trap response is artificially delayed to a minimum duration. This prevents an attacker from distinguishing the honeypot from a real page by measuring response time.

The default minimum is 1000ms. Override it globally or per trap:

```env
NOT_TODAY_HONEY_MIN_RESPONSE_MS=1000
NOT_TODAY_HONEY_WP_MIN_RESPONSE_MS=800
NOT_TODAY_HONEY_PMA_MIN_RESPONSE_MS=1500
```

See [Configuration → Timing](/configuration#timing-normalization) for details.

## Whitelisting

IPs in the whitelist are never blocked. They still trigger events (with `isTest() = true`) so you can test your listener setup from a local machine without creating real detection records.

```env
NOT_TODAY_HONEY_WHITELIST=127.0.0.1,10.0.0.5
```

See [Configuration → Whitelist](/configuration#whitelist).
