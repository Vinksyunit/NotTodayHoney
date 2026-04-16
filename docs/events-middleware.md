# Events & Middleware

## Events

NotTodayHoney dispatches a Laravel event each time an alert threshold is reached. Listen to these events to trigger notifications, logging, or incident response.

### Available Events

| Event class | When it fires |
|-------------|---------------|
| `AttackerProbingEvent` | IP visits a trap N times within the time window |
| `AttackerIntrusionAttemptEvent` | IP submits a login form |
| `AttackerAttackingEvent` | IP submits a login form with a known leaked password |

All three events implement `AttackerAlertEvent` and expose the same methods:

| Method | Return type | Description |
|--------|-------------|-------------|
| `getIp()` | `string` | The attacker's IP address |
| `getAttemptCount()` | `int` | Number of attempts in the current window |
| `getAlertLevel()` | `AlertLevel` | The alert level enum value |
| `isTest()` | `bool` | `true` if the IP is whitelisted (safe to use in testing) |

### Registering Listeners

Laravel automatically discovers listener classes via [event auto-discovery](https://laravel.com/docs/events#event-discovery). Create a listener class with a type-hinted `handle` method and it will be registered automatically — no manual registration needed:

```php
namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Vinksyunit\NotTodayHoney\Contracts\AttackerAlertEvent;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;

class LogHoneypotAlert
{
    public function handle(AttackerAlertEvent $event): void
    {
        match ($event->getAlertLevel()) {
            AlertLevel::PROBING => Log::info("Honeypot probe from {$event->getIp()} ({$event->getAttemptCount()} visits)"),
            AlertLevel::INTRUSION_ATTEMPT => Log::warning("Login attempt from {$event->getIp()}"),
            AlertLevel::ATTACKING => Log::critical("Known leaked credentials used from {$event->getIp()}"),
        };
    }
}
```

Alternatively, for quick prototyping you can use closures in a service provider:

```php
use Vinksyunit\NotTodayHoney\Events\AttackerProbingEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

// In a ServiceProvider boot() method:
Event::listen(AttackerProbingEvent::class, function (AttackerProbingEvent $event) {
    Log::info("Honeypot probe from {$event->getIp()} ({$event->getAttemptCount()} visits)");
});
```

::: warning Do not mix both approaches
If you register a listener class via `Event::listen()` in a service provider, it will fire **twice** — once from auto-discovery and once from your manual registration. Use one approach or the other.
:::

## Middleware

The `nottodayhoney.block` middleware checks if the requesting IP is currently blocked. If it is, the request receives a 403 response. Whitelisted IPs always pass through.

### Global Protection

Apply to every request in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotBlockMiddleware::class);
})
```

### Route Group Protection

Apply to specific routes only:

```php
Route::middleware('nottodayhoney.block')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/api/login', [AuthController::class, 'login']);
});
```

## Facade API

The `NotTodayHoney` facade provides programmatic access to detection data:

```php
use Vinksyunit\NotTodayHoney\Facades\NotTodayHoney;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;

// Check if an IP is currently blocked
NotTodayHoney::isBlocked('1.2.3.4'); // bool

// Get all currently blocked IPs
NotTodayHoney::getBlockedIps(); // Collection<AttackerDetection>

// Unblock an IP
NotTodayHoney::unblock('1.2.3.4');

// Get the detection record for an IP
NotTodayHoney::getDetection('1.2.3.4'); // ?AttackerDetection

// Get all detections at a specific alert level
NotTodayHoney::getDetectionsByLevel(AlertLevel::ATTACKING); // Collection<AttackerDetection>
```
