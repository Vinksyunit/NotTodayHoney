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

```php
use Vinksyunit\NotTodayHoney\Events\AttackerProbingEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerIntrusionAttemptEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

// In a ServiceProvider or using the #[ListensTo] attribute:

Event::listen(AttackerProbingEvent::class, function (AttackerProbingEvent $event) {
    Log::info("Honeypot probe from {$event->getIp()} ({$event->getAttemptCount()} visits)");
});

Event::listen(AttackerIntrusionAttemptEvent::class, function (AttackerIntrusionAttemptEvent $event) {
    Log::warning("Login attempt from {$event->getIp()}");
    // Notify your security team, create a ticket, etc.
});

Event::listen(AttackerAttackingEvent::class, function (AttackerAttackingEvent $event) {
    Log::critical("Known leaked credentials used from {$event->getIp()}");
    // This is a serious signal — escalate immediately
});
```

## Middleware

The `honeypot.block` middleware checks if the requesting IP is currently blocked. If it is, the request receives a 403 response. Whitelisted IPs always pass through.

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
Route::middleware('honeypot.block')->group(function () {
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
