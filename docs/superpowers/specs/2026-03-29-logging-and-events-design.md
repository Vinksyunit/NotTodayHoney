# Logging & Events Design — NotTodayHoney

**Date:** 2026-03-29
**Status:** Approved

## Problem

The config defined `notify` and `channels` per alert level, but no code ever read them. The package dispatched events into the void with no built-in observability, and the `notify`/`channels` keys implied the package would handle Slack/mail notification routing — which is the host app's responsibility.

## Intent

Split concerns cleanly:
- **Logging**: always-on, built-in, uses the host app's default Laravel log channel. Gives operators immediate observability with zero configuration.
- **Events**: already dispatched when thresholds are reached. The package's responsibility ends there. Users register their own listeners for custom notifications (Slack, mail, etc.).

## Changes

### 1. Config: replace `notify` + `channels` with `log_level`

Remove `notify` and `channels` from all three alert level entries. Add `log_level`:

```php
'probing' => [
    'threshold'       => env('NOT_TODAY_HONEY_PROBING_THRESHOLD', 3),
    'time_window'     => env('NOT_TODAY_HONEY_PROBING_TIME_WINDOW', 1440),
    'mark_as_insecure'=> env('NOT_TODAY_HONEY_PROBING_BLOCK', true),
    'duration'        => env('NOT_TODAY_HONEY_PROBING_DURATION', 20),
    'log_level'       => env('NOT_TODAY_HONEY_PROBING_LOG_LEVEL', 'info'),
],
'intrusion_attempt' => [
    'threshold'       => env('NOT_TODAY_HONEY_INTRUSION_THRESHOLD', 1),
    'time_window'     => env('NOT_TODAY_HONEY_INTRUSION_TIME_WINDOW', 1440),
    'mark_as_insecure'=> env('NOT_TODAY_HONEY_INTRUSION_BLOCK', true),
    'duration'        => env('NOT_TODAY_HONEY_INTRUSION_DURATION', 1440),
    'log_level'       => env('NOT_TODAY_HONEY_INTRUSION_LOG_LEVEL', 'warning'),
],
'attacking' => [
    'threshold'       => env('NOT_TODAY_HONEY_ATTACKING_THRESHOLD', 1),
    'time_window'     => env('NOT_TODAY_HONEY_ATTACKING_TIME_WINDOW', 1440),
    'mark_as_insecure'=> env('NOT_TODAY_HONEY_ATTACKING_BLOCK', true),
    'duration'        => env('NOT_TODAY_HONEY_ATTACKING_DURATION', 43200),
    'log_level'       => env('NOT_TODAY_HONEY_ATTACKING_LOG_LEVEL', 'critical'),
],
```

Default log levels: `info` / `warning` / `critical` (escalating).

### 2. Logging in `AttackerDetectionService::triggerAlert()`

After blocking the IP, before dispatching the event, add:

```php
$logLevel = config("not-today-honey.alerts.{$level->value}.log_level", 'warning');
$trapName = $detection->trapAttempts()->latest()->value('trap_name');

Log::log($logLevel, '[NotTodayHoney] Attacker detected', [
    'ip'            => $detection->ip,
    'alert_level'   => $level->value,
    'attempt_count' => $detection->attempt_count,
    'blocked_until' => $detection->blocked_until?->toIso8601String(),
    'trap_name'     => $trapName,
]);
```

- Uses `Log::log()` — routes to the host app's **default** log channel, no channel selection by the package.
- `trap_name` is resolved from the most recent `TrapAttempt` for this detection.
- Whitelisted IPs never reach `triggerAlert()` (they return early in `recordAttempt()`), so no special test-mode handling needed here.

### 3. Events — no code changes

The three events (`AttackerProbingEvent`, `AttackerIntrusionAttemptEvent`, `AttackerAttackingEvent`) are already dispatched correctly. No changes needed.

Users who want custom notifications register their own listeners:

```php
// In AppServiceProvider or EventServiceProvider
Event::listen(AttackerAttackingEvent::class, function (AttackerAttackingEvent $event) {
    // send Slack message, email, PagerDuty alert, etc.
    // $event->getIp(), $event->getAlertLevel(), $event->getAttemptCount()
});
```

### 4. Documentation spec update

`docs/superpowers/specs/2026-03-29-documentation-design.md` references `notify` and `channels` in the Configuration page outline (line 112). That line should be updated to reference `log_level` instead, and the `events-middleware.md` page should clarify the split: automatic logging vs. event-driven custom notifications.

## Out of Scope

- Channel selection by the package (host app controls log routing)
- Built-in Slack/mail notification classes
- A `notify` toggle to disable logging (logging is always-on)
- New listener classes inside the package
