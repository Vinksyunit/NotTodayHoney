# NotTodayHoney

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vinksyunit/not-today-honey.svg?style=flat-square)](https://packagist.org/packages/vinksyunit/not-today-honey)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/Vinksyunit/NotTodayHoney/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Vinksyunit/NotTodayHoney/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/Vinksyunit/NotTodayHoney/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/Vinksyunit/NotTodayHoney/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vinksyunit/not-today-honey.svg?style=flat-square)](https://packagist.org/packages/vinksyunit/not-today-honey)
![PHP Version Require](https://img.shields.io/packagist/php-v/vinksyunit/not-today-honey?logo=php)
![Laravel Version](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel)
![License](https://img.shields.io/packagist/l/vinksyunit/not-today-honey)

A Laravel honeypot package that simulates attractive web pages (WordPress wp-admin, phpMyAdmin) to detect and block attackers.

## Features

- Realistic honeypot traps (WordPress login, phpMyAdmin, Generic Admin)
- 3-level alert system (Probing, Intrusion Attempt, Attacking)
- Known leaked credentials detection via bcrypt hash comparison
- Automatic IP blocking with configurable durations per alert level
- Event-driven notifications (Slack, mail, log via listeners)
- `honeypot.block` middleware to protect your app routes
- Facade for programmatic access to blocked IPs and detection data
- Configurable trap behaviors (403, 500, infinite loading, fake success)

## Requirements

- PHP 8.3+
- Laravel 12

## Installation

Install the package via Composer:

```bash
composer require vinksyunit/not-today-honey
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="not-today-honey-config"
```

Run the database migrations:

```bash
php artisan migrate
```

## Configuration

The config file (`config/not-today-honey.php`) contains four main sections:

### Whitelist

IPs that should never be blocked. Environment variable: `NOT_TODAY_HONEY_WHITELIST` (default: `127.0.0.1`).

```php
'whitelist' => explode(',', env('NOT_TODAY_HONEY_WHITELIST', '127.0.0.1')),
```

### Credentials

Known leaked usernames and bcrypt password hashes to detect known attacks:

```php
'credentials' => [
    'usernames' => explode(',', env('NOT_TODAY_HONEY_USERNAMES', 'admin,administrator,webmaster,root,maintenance')),
    'passwords' => [
        [
            'id' => 'rockyou_top_1',
            'hash' => env('NOT_TODAY_HONEY_HASH_1', '...'), // bcrypt hash
        ],
    ],
],
```

When a known password hash is detected, the alert level is automatically escalated to "Attacking".

### Alerts

Configuration for each of the three alert levels:

```php
'alerts' => [
    'probing' => [
        'threshold' => 3,                                      // Alerts after N occurrences
        'time_window' => 1440,                                 // Within N minutes
        'mark_as_insecure' => true,                           // Whether to block the IP
        'duration' => 1440,                                    // Block duration in minutes
        'notify' => false,                                     // Send notifications
        'channels' => ['stack'],                              // Notification channels
    ],
    'intrusion_attempt' => [
        'threshold' => 1,
        'time_window' => 1440,
        'mark_as_insecure' => true,
        'duration' => 10080,                                   // 7 days
        'notify' => true,
        'channels' => ['stack', 'slack'],
    ],
    'attacking' => [
        'threshold' => 1,
        'time_window' => 1440,
        'mark_as_insecure' => true,
        'duration' => null,                                    // Permanent block
        'notify' => true,
        'channels' => ['stack', 'slack', 'mail'],
    ],
],
```

### Traps

Enable/disable individual honeypot traps and configure their behavior:

```php
'traps' => [
    'wordpress' => [
        'enabled' => env('NOT_TODAY_HONEY_WP_ENABLED', true),
        'path' => env('NOT_TODAY_HONEY_WP_PATH', '/wp-admin'),
        'behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_WP_BEHAVIOR', '403')),
        'specific' => [
            'version' => env('NOT_TODAY_HONEY_WP_VERSION', '6.4.2'),
        ],
    ],
    'phpmyadmin' => [
        'enabled' => env('NOT_TODAY_HONEY_PMA_ENABLED', true),
        'path' => env('NOT_TODAY_HONEY_PMA_PATH', '/phpmyadmin'),
        'behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_PMA_BEHAVIOR', '403')),
        'specific' => [
            'pma_version' => env('NOT_TODAY_HONEY_PMA_VERSION', '5.2.1'),
        ],
    ],
    'generic_admin' => [
        'enabled' => env('NOT_TODAY_HONEY_GENERIC_ENABLED', true),
        'path' => env('NOT_TODAY_HONEY_GENERIC_PATH', '/admin'),
        'behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_GENERIC_BEHAVIOR', '403')),
        'specific' => [
            'title' => env('NOT_TODAY_HONEY_GENERIC_TITLE', 'Control Panel'),
        ],
    ],
],
```

## Trap Behaviors

Available trap response behaviors:

| Behavior | Response | Purpose |
|----------|----------|---------|
| `FORBIDDEN` | 403 Forbidden | Standard access denied |
| `ERROR` | 500 Internal Server Error | Simulate server error |
| `INFINITE_LOADING` | Tarpitting | Stall attacker with slow response |
| `FAKE_SUCCESS` | 200 OK (empty dashboard) | Show fake success page (if credentials match) |

## Protecting Routes with Middleware

### Global Protection

Add the middleware globally in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotBlockMiddleware::class);
})
```

### Route Group Protection

Or protect specific routes by applying the middleware to route groups:

```php
Route::middleware('honeypot.block')->group(function () {
    Route::get('/api/users', [UserController::class, 'index']);
    Route::post('/admin/login', [AdminController::class, 'login']);
});
```

The middleware checks if the requesting IP is blocked and returns a 403 response if so.

## Listening to Events

Three events are dispatched at different alert levels:

```php
use Vinksyunit\NotTodayHoney\Events\AttackerProbingEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerIntrusionAttemptEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

// In EventServiceProvider or using #[ListensTo] attribute
Event::listen(AttackerAttackingEvent::class, function (AttackerAttackingEvent $event) {
    Log::critical('Known password used from ' . $event->getIp());
    // Send alerts, trigger incident response, etc.
});

Event::listen(AttackerIntrusionAttemptEvent::class, function (AttackerIntrusionAttemptEvent $event) {
    Log::warning('Login attempt from ' . $event->getIp());
});

Event::listen(AttackerProbingEvent::class, function (AttackerProbingEvent $event) {
    Log::info('Honeypot probe from ' . $event->getIp());
});
```

Each event provides methods to access:
- `getIp()` - Attacker IP address
- `getAttemptCount()` - Number of attempts in current window
- `getAlertLevel()` - Current alert level (AlertLevel enum)
- `isTest()` - Whether IP is whitelisted (test mode)

## Facade API

Use the NotTodayHoney facade for programmatic access:

```php
use Vinksyunit\NotTodayHoney\Facades\NotTodayHoney;

// Check if an IP is blocked
NotTodayHoney::isBlocked('1.2.3.4');  // bool

// Get all currently blocked IPs
NotTodayHoney::getBlockedIps();  // Collection

// Unblock an IP
NotTodayHoney::unblock('1.2.3.4');

// Get detection record for an IP
NotTodayHoney::getDetection('1.2.3.4');  // ?AttackerDetection

// Get all detections at a specific alert level
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;
NotTodayHoney::getDetectionsByLevel(AlertLevel::ATTACKING);  // Collection
```

## Artisan Commands

Check blocked IPs and manage them:

```bash
# List all currently blocked IPs with their details
php artisan honey:status

# Unblock a specific IP
php artisan honey:unblock 1.2.3.4
```

## Testing

Run the test suite:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Vinksyunit](https://github.com/Vinksyunit)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
