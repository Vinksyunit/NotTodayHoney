# Logging & Events Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add always-on structured logging to `AttackerDetectionService` and clean up the config by replacing the unused `notify`/`channels` keys with a configurable `log_level` per alert level.

**Architecture:** All changes are in two files — `config/not-today-honey.php` and `AttackerDetectionService`. The service's `triggerAlert()` method gets a single `Log::log()` call after blocking, before event dispatch. No new classes. Events are already dispatched correctly and need no changes.

**Tech Stack:** Laravel 12+, PHP 8.4+, Pest

---

## File Map

| File | Change |
|---|---|
| `tests/Feature/AttackerDetectionServiceTest.php` | Add 5 new tests for logging behaviour (TDD) |
| `config/not-today-honey.php` | Remove `notify` + `channels` from alert levels; add `log_level` |
| `src/Services/AttackerDetectionService.php` | Add `Log` import; add `Log::log()` call in `triggerAlert()` |
| `docs/superpowers/specs/2026-03-29-documentation-design.md` | Update stale reference to `notify`/`channels` on line 112 |

---

## Task 1: Write failing tests for logging

**Files:**
- Modify: `tests/Feature/AttackerDetectionServiceTest.php`

- [ ] **Step 1: Add imports at the top of the test file**

Add two new `use` statements after the existing ones (after line 11):

```php
use Illuminate\Support\Facades\Log;
use Vinksyunit\NotTodayHoney\Models\TrapAttempt;
```

- [ ] **Step 2: Add the five new tests at the end of the file**

Append after the last test (`it('does not block whitelisted IPs', ...)`):

```php
it('logs at info level when probing threshold is reached', function () {
    Log::spy();
    config()->set('not-today-honey.alerts.probing.threshold', 1);

    $this->service->recordAttempt('10.0.0.1', AlertLevel::PROBING);

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message, $context) =>
            $level === 'info' &&
            $message === '[NotTodayHoney] Attacker detected' &&
            $context['ip'] === '10.0.0.1' &&
            $context['alert_level'] === 'probing'
        );
});

it('logs at warning level when intrusion_attempt threshold is reached', function () {
    Log::spy();
    config()->set('not-today-honey.alerts.intrusion_attempt.threshold', 1);

    $this->service->recordAttempt('10.0.0.2', AlertLevel::INTRUSION_ATTEMPT);

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message, $context) =>
            $level === 'warning' &&
            $context['alert_level'] === 'intrusion_attempt'
        );
});

it('logs at critical level when attacking threshold is reached', function () {
    Log::spy();
    config()->set('not-today-honey.alerts.attacking.threshold', 1);

    $this->service->recordAttempt('10.0.0.3', AlertLevel::ATTACKING);

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message, $context) =>
            $level === 'critical' &&
            $context['alert_level'] === 'attacking'
        );
});

it('uses log_level from config when logging', function () {
    Log::spy();
    config()->set('not-today-honey.alerts.probing.threshold', 1);
    config()->set('not-today-honey.alerts.probing.log_level', 'debug');

    $this->service->recordAttempt('10.0.0.4', AlertLevel::PROBING);

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message, $context) => $level === 'debug');
});

it('includes trap_name from latest trap attempt in log context', function () {
    Log::spy();
    config()->set('not-today-honey.alerts.probing.threshold', 2);

    $this->service->recordAttempt('10.0.0.5', AlertLevel::PROBING);

    $detection = AttackerDetection::first();
    TrapAttempt::create([
        'attacker_detection_id' => $detection->id,
        'trap_name'             => 'wordpress',
        'path'                  => '/wp-login.php',
        'method'                => 'GET',
        'headers'               => [],
        'created_at'            => now(),
    ]);

    $this->service->recordAttempt('10.0.0.5', AlertLevel::PROBING);

    Log::shouldHaveReceived('log')
        ->once()
        ->withArgs(fn ($level, $message, $context) =>
            $context['trap_name'] === 'wordpress'
        );
});
```

- [ ] **Step 3: Run the new tests to confirm they fail**

```bash
composer test -- --filter "logs at|uses log_level|includes trap_name"
```

Expected: 5 FAIL — `Log::shouldHaveReceived` finds no calls because the service doesn't log yet.

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/AttackerDetectionServiceTest.php
git commit -m "test: write failing tests for structured logging in AttackerDetectionService"
```

---

## Task 2: Update config — replace `notify`/`channels` with `log_level`

**Files:**
- Modify: `config/not-today-honey.php`

- [ ] **Step 1: Replace the `alerts` section**

Find the entire `'alerts'` array (lines 55–80) and replace it with:

```php
    /*
    |--------------------------------------------------------------------------
    | Alert Levels Configuration
    |--------------------------------------------------------------------------
    |
    | Probing: Simple visite du piège (reconnaissance/exploration).
    | Intrusion Attempt: Tentative de login (quelconque).
    | Attacking: Utilisation d'un mot de passe présent dans la liste 'passwords'.
    |
    | log_level: Niveau de log Laravel utilisé lors du déclenchement de l'alerte.
    | Valeurs possibles : debug, info, notice, warning, error, critical, alert, emergency.
    |
    */
    'alerts' => [
        'probing' => [
            'threshold'        => env('NOT_TODAY_HONEY_PROBING_THRESHOLD', 3),
            'time_window'      => env('NOT_TODAY_HONEY_PROBING_TIME_WINDOW', 1440), // Minutes (default: 1 day)
            'mark_as_insecure' => env('NOT_TODAY_HONEY_PROBING_BLOCK', true),
            'duration'         => env('NOT_TODAY_HONEY_PROBING_DURATION', 20), // Minutes
            'log_level'        => env('NOT_TODAY_HONEY_PROBING_LOG_LEVEL', 'info'),
        ],
        'intrusion_attempt' => [
            'threshold'        => env('NOT_TODAY_HONEY_INTRUSION_THRESHOLD', 1),
            'time_window'      => env('NOT_TODAY_HONEY_INTRUSION_TIME_WINDOW', 1440), // Minutes (default: 1 day)
            'mark_as_insecure' => env('NOT_TODAY_HONEY_INTRUSION_BLOCK', true),
            'duration'         => env('NOT_TODAY_HONEY_INTRUSION_DURATION', 1440), // Minutes (24 hours)
            'log_level'        => env('NOT_TODAY_HONEY_INTRUSION_LOG_LEVEL', 'warning'),
        ],
        'attacking' => [
            'threshold'        => env('NOT_TODAY_HONEY_ATTACKING_THRESHOLD', 1),
            'time_window'      => env('NOT_TODAY_HONEY_ATTACKING_TIME_WINDOW', 1440), // Minutes (default: 1 day)
            'mark_as_insecure' => env('NOT_TODAY_HONEY_ATTACKING_BLOCK', true),
            'duration'         => env('NOT_TODAY_HONEY_ATTACKING_DURATION', 43200), // Minutes (30 days)
            'log_level'        => env('NOT_TODAY_HONEY_ATTACKING_LOG_LEVEL', 'critical'),
        ],
    ],
```

- [ ] **Step 2: Run the full test suite**

```bash
composer test
```

Expected: The 5 new logging tests still FAIL (service not updated yet). All 38 existing tests still PASS — removing `notify`/`channels` from config has no effect on existing code since nothing read those keys.

- [ ] **Step 3: Commit**

```bash
git add config/not-today-honey.php
git commit -m "config: replace notify/channels with log_level per alert level"
```

---

## Task 3: Add logging to `AttackerDetectionService::triggerAlert()`

**Files:**
- Modify: `src/Services/AttackerDetectionService.php`

- [ ] **Step 1: Add the `Log` facade import**

After the existing `use Illuminate\Support\Facades\Event;` line (line 8), add:

```php
use Illuminate\Support\Facades\Log;
```

- [ ] **Step 2: Replace `triggerAlert()` with the logging version**

Replace the entire `triggerAlert()` method (lines 80–98) with:

```php
    /**
     * Trigger an alert for a specific level and block the IP.
     */
    protected function triggerAlert(AttackerDetection $detection, AlertLevel $level, ?int $blockDuration): void
    {
        // Block the IP
        if ($blockDuration !== null) {
            $detection->blockUntil(now()->addMinutes($blockDuration), $level);
        } else {
            // Permanent block (null duration): set to 100 years in the future
            $detection->blockUntil(now()->addYears(100), $level);
        }

        // Log the detection to the application's default log channel
        $logLevel = config("not-today-honey.alerts.{$level->value}.log_level", 'warning');
        $trapName = $detection->trapAttempts()->latest()->value('trap_name');

        Log::log($logLevel, '[NotTodayHoney] Attacker detected', [
            'ip'            => $detection->ip,
            'alert_level'   => $level->value,
            'attempt_count' => $detection->attempt_count,
            'blocked_until' => $detection->blocked_until?->toIso8601String(),
            'trap_name'     => $trapName,
        ]);

        // Dispatch the appropriate event
        $eventClass = match ($level) {
            AlertLevel::PROBING           => AttackerProbingEvent::class,
            AlertLevel::INTRUSION_ATTEMPT => AttackerIntrusionAttemptEvent::class,
            AlertLevel::ATTACKING         => AttackerAttackingEvent::class,
        };

        Event::dispatch(new $eventClass($detection));
    }
```

- [ ] **Step 3: Run the full test suite**

```bash
composer test
```

Expected: All 43 tests PASS (38 existing + 5 new logging tests).

- [ ] **Step 4: Commit**

```bash
git add src/Services/AttackerDetectionService.php
git commit -m "feat: add structured logging to AttackerDetectionService on alert threshold"
```

---

## Task 4: Update stale documentation spec

**Files:**
- Modify: `docs/superpowers/specs/2026-03-29-documentation-design.md`

- [ ] **Step 1: Update the Configuration page outline**

Find line 112:
```
- **Alert Levels** — table showing threshold / time_window / mark_as_insecure / duration / notify / channels for each of probing, intrusion_attempt, attacking
```

Replace with:
```
- **Alert Levels** — table showing threshold / time_window / mark_as_insecure / duration / log_level for each of probing, intrusion_attempt, attacking; note that log_level accepts any standard Laravel log level string (debug, info, notice, warning, error, critical, alert, emergency)
```

- [ ] **Step 2: Run the full test suite to confirm nothing regressed**

```bash
composer test
```

Expected: All 43 tests PASS.

- [ ] **Step 3: Commit**

```bash
git add -f docs/superpowers/specs/2026-03-29-documentation-design.md
git commit -m "docs: update configuration spec to reflect log_level replacing notify/channels"
```
