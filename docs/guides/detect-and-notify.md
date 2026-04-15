# Detect & Get Notified

NotTodayHoney detects attackers by attracting them to realistic decoy pages and classifying what they do there. This guide explains how that classification works and how to wire up notifications.

## How detection works

Every request to a trap URL goes through the same pipeline:

1. The trap records the visit as a **trap attempt**
2. The attempt is classified against [alert level thresholds](/configuration#alert-levels)
3. If a threshold is reached, a detection record is written and a Laravel **event** is dispatched
4. If `mark_as_insecure` is `true` (the default), the IP is blocked for the configured duration

The three alert levels represent increasing attacker intent:

| Level | Trigger | Default block |
|-------|---------|---------------|
| `probing` | N visits to any trap within the time window | 20 minutes |
| `intrusion_attempt` | Login form submitted | 24 hours |
| `attacking` | Login with a known leaked password | 30 days |

See [Configuration → Alert Levels](/configuration#alert-levels) for threshold and duration settings.

## Wiring up notifications

Each alert level dispatches a dedicated event. Register listeners in a service provider or using the `#[ListensTo]` attribute:

```php
use Vinksyunit\NotTodayHoney\Events\AttackerProbingEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerIntrusionAttemptEvent;
use Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

Event::listen(AttackerProbingEvent::class, function (AttackerProbingEvent $event) {
    Log::info("Honeypot probe from {$event->getIp()} ({$event->getAttemptCount()} visits)");
});

Event::listen(AttackerIntrusionAttemptEvent::class, function (AttackerIntrusionAttemptEvent $event) {
    // Example: post to a Slack webhook
    Http::post(config('services.slack.webhook'), [
        'text' => "⚠️ Login attempt on honeypot from {$event->getIp()}",
    ]);
});

Event::listen(AttackerAttackingEvent::class, function (AttackerAttackingEvent $event) {
    Log::critical("Known leaked credentials used from {$event->getIp()}");
    // Escalate immediately — this is a serious signal
});
```

All three events share the same interface. See [Events & Middleware](/events-middleware#events) for the full method reference.

## Campaign detection

When the global trap hit rate exceeds the configured limit, `TrapCampaignDetectedEvent` is dispatched once (on the first breach). This signals a coordinated scan — many IPs hitting your traps simultaneously.

```php
use Vinksyunit\NotTodayHoney\Events\TrapCampaignDetectedEvent;

Event::listen(TrapCampaignDetectedEvent::class, function () {
    Log::alert('Coordinated attack campaign detected across honeypot traps');
    // Alert your security team
});
```

See [Configuration → Rate Limiting](/configuration#rate-limiting) to tune the global threshold.

## Testing your setup

Whitelisted IPs (default: `127.0.0.1`) still trigger all events, but with `isTest()` returning `true` and no block written. Use this to verify your listeners fire without polluting production detection records:

```php
Event::listen(AttackerProbingEvent::class, function (AttackerProbingEvent $event) {
    if ($event->isTest()) {
        Log::debug('Test probe received — listener is wired up correctly');
        return;
    }
    // real handling
});
```

Visit `/wp-admin` from your local machine to trigger a test probe.
