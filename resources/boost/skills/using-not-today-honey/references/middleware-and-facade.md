# Blocking IPs: Middleware & Facade

## Middleware `nottodayhoney.block`

Alias registered by the service provider → `Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotBlockMiddleware`. Blocked IPs get `403`. Whitelisted IPs always pass through. **Do not** apply it to the package's own trap routes.

### Global registration

@verbatim
<code-snippet name="Global middleware in bootstrap/app.php" lang="php">
use Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotBlockMiddleware;

->withMiddleware(function (Middleware $middleware) {
    $middleware->append(HoneypotBlockMiddleware::class);
})
</code-snippet>
@endverbatim

### Route-group registration

@verbatim
<code-snippet name="Protect a route group" lang="php">
Route::middleware('nottodayhoney.block')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::post('/api/login', [AuthController::class, 'login']);
});
</code-snippet>
@endverbatim

## Facade API

@verbatim
<code-snippet name="Programmatic block queries" lang="php">
use Vinksyunit\NotTodayHoney\Facades\NotTodayHoney;
use Vinksyunit\NotTodayHoney\Enums\AlertLevel;

NotTodayHoney::isBlocked('1.2.3.4');                         // bool
NotTodayHoney::getBlockedIps();                              // Collection<AttackerDetection>
NotTodayHoney::getDetection('1.2.3.4');                      // ?AttackerDetection
NotTodayHoney::getDetectionsByLevel(AlertLevel::ATTACKING);  // Collection<AttackerDetection>
NotTodayHoney::unblock('1.2.3.4');                           // void — wipes detection records
</code-snippet>
@endverbatim

Inject the class directly in long-lived code rather than resolving the facade:

@verbatim
<code-snippet name="Injected usage" lang="php">
use Vinksyunit\NotTodayHoney\NotTodayHoney;

public function __construct(private readonly NotTodayHoney $honey) {}

public function handle(): void
{
    if ($this->honey->isBlocked(request()->ip())) {
        abort(403);
    }
}
</code-snippet>
@endverbatim

## `AttackerDetection` model

Returned by `getDetection()`, `getBlockedIps()`, and event `getDetection()`. Attributes: `ip`, `alert_level` (`AlertLevel` enum cast), `attempt_count`, `blocked_until` (`CarbonImmutable|null`). `AttackerDetection::blocked()` scope selects currently-blocked rows.

## Guidance

- `unblock()` wipes the IP's detection history entirely. To reduce severity without a full reset, mutate `blocked_until` directly on the model.
- The middleware hits the DB on every request. For high-traffic apps, combine with an upstream cache (Redis) or reverse-proxy denylist populated from `getBlockedIps()`.
