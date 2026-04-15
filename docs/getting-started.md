# Getting Started

## Requirements

- PHP 8.4+
- Laravel 12+

## Installation

```bash
composer require vinksyunit/not-today-honey
```

## Publish Configuration and Migrations

```bash
php artisan vendor:publish --tag="not-today-honey-config"
php artisan vendor:publish --tag="not-today-honey-migrations"
```

This creates `config/not-today-honey.php` with all available options, and publishes the migration files to `database/migrations/`.

## Run Migrations

```bash
php artisan migrate
```

This creates three tables:

| Table | Purpose |
|-------|---------|
| `not_today_honey_attacker_detections` | IP detection records and block status |
| `not_today_honey_trap_attempts` | Individual trap visit records |
| `not_today_honey_credential_attempts` | Credential submission records |

## Add the Middleware

Add `nottodayhoney.block` globally in `bootstrap/app.php` to deny blocked IPs from your entire application:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotBlockMiddleware::class);
})
```

See [Events & Middleware](/events-middleware) for route-level middleware usage.

## Verify It Works

Start your application and visit one of the default trap URLs:

- `/wp-admin` → WordPress login trap
- `/phpmyadmin` → phpMyAdmin trap
- `/admin/login` → Generic admin trap

Each visit is recorded as a **Probing** event. After 3 visits within 24 hours, the IP is blocked for 20 minutes.
