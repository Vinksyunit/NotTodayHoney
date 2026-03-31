# Configuration

The published config file lives at `config/not-today-honey.php`.

## Whitelist

IPs that are never blocked. They still trigger events with `is_test = true`, so you can test listeners safely.

```php
'whitelist' => explode(',', env('NOT_TODAY_HONEY_WHITELIST', '127.0.0.1')),
```

Set `NOT_TODAY_HONEY_WHITELIST=127.0.0.1,10.0.0.1` in `.env` for multiple IPs.

## Rate Limiting

Two independent rate limits protect the traps from being overwhelmed.

**Per-IP limiting** caps how many times a single IP can hit any trap within a time window. When exceeded, the request receives a `429` response — no event is dispatched and no detection record is written.

**Global limiting** caps total trap hits across all IPs. When exceeded, a `429` is returned and `TrapCampaignDetectedEvent` is dispatched once (on the first breach). This is a signal that a coordinated campaign is underway.

::: tip
Whitelisted IPs bypass both rate limits.
:::

```php
'rate_limiting' => [
    'per_ip' => [
        'enabled'       => env('NOT_TODAY_HONEY_RATE_IP_ENABLED', true),
        'max_hits'      => env('NOT_TODAY_HONEY_RATE_IP_MAX', 30),
        'decay_minutes' => env('NOT_TODAY_HONEY_RATE_IP_DECAY', 1),
    ],
    'global' => [
        'enabled'       => env('NOT_TODAY_HONEY_RATE_GLOBAL_ENABLED', true),
        'max_hits'      => env('NOT_TODAY_HONEY_RATE_GLOBAL_MAX', 200),
        'decay_minutes' => env('NOT_TODAY_HONEY_RATE_GLOBAL_DECAY', 1),
    ],
],
```

| Key | Default | Purpose |
|-----|---------|---------|
| `NOT_TODAY_HONEY_RATE_IP_ENABLED` | `true` | Enable per-IP rate limiting |
| `NOT_TODAY_HONEY_RATE_IP_MAX` | `30` | Max hits per IP per window |
| `NOT_TODAY_HONEY_RATE_IP_DECAY` | `1` | Window size in minutes |
| `NOT_TODAY_HONEY_RATE_GLOBAL_ENABLED` | `true` | Enable global rate limiting |
| `NOT_TODAY_HONEY_RATE_GLOBAL_MAX` | `200` | Max total hits per window |
| `NOT_TODAY_HONEY_RATE_GLOBAL_DECAY` | `1` | Window size in minutes |

Listen to `TrapCampaignDetectedEvent` to react to global limit breaches:

```php
use Vinksyunit\NotTodayHoney\Events\TrapCampaignDetectedEvent;

Event::listen(TrapCampaignDetectedEvent::class, function (TrapCampaignDetectedEvent $event) {
    // Alert your security team — this is a coordinated attack signal
});
```

## Timing Normalization

Every trap response takes at least `min_response_ms` milliseconds before being sent. This prevents timing-based reconnaissance: an attacker probing many URLs cannot distinguish a honeypot from a real page by measuring how fast it responds.

```php
'timing' => [
    'min_response_ms' => env('NOT_TODAY_HONEY_MIN_RESPONSE_MS', 1000),
],
```

Override per trap if needed:

```env
NOT_TODAY_HONEY_WP_MIN_RESPONSE_MS=800
NOT_TODAY_HONEY_PMA_MIN_RESPONSE_MS=1200
NOT_TODAY_HONEY_GENERIC_MIN_RESPONSE_MS=null
```

Set a per-trap variable to `null` (or leave it unset) to fall back to the global value.

## Credentials

Known usernames and passwords. When an attacker submits credentials that match this list, the alert level escalates immediately to **Attacking**.

### Usernames

```php
'usernames' => explode(',', env('NOT_TODAY_HONEY_USERNAMES', 'admin,administrator,webmaster,root,maintenance')),
```

### Password detection

Passwords are compared using **truncated SHA256 with a salt** — not bcrypt. The submitted password is salted and hashed, and only the first 8 hex characters are compared. This prevents timing attacks (constant-time comparison on a short string) while making rainbow tables impractical.

```php
'passwords' => [
    'include_defaults' => env('NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST') === null,
    'custom'           => array_filter(explode(',', env('NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST', ''))),
    'salt'             => env('NOT_TODAY_HONEY_PASSWORD_SALT', 'not-today-honey'),
],
```

**`include_defaults`** — enables the built-in list of common passwords (e.g. `letmein`, `iloveyou`). Automatically disabled once you set `NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST`.

**`custom`** — comma-separated truncated hashes generated with `honey:hash-password`.

**`salt`** — generated once with `honey:generate-salt` and stored in `.env`. Changing the salt invalidates all existing custom hashes.

See [Compromised Passwords guide](/guides/passwords) for the full setup walkthrough, or [Artisan Commands](/commands) for command reference.

## Alert Levels

Three levels of increasing severity, each independently configurable:

| Key | `probing` | `intrusion_attempt` | `attacking` |
|-----|-----------|---------------------|-------------|
| `threshold` | 3 | 1 | 1 |
| `time_window` (minutes) | 1440 (1 day) | 1440 | 1440 |
| `mark_as_insecure` | `true` | `true` | `true` |
| `duration` (minutes) | 20 | 1440 (24h) | 43200 (30 days) |
| `log_level` | `info` | `warning` | `critical` |

**`threshold`** — number of events before the alert fires and the IP is blocked.

**`time_window`** — events are counted within this rolling window (minutes).

**`mark_as_insecure`** — set to `false` to record without blocking (monitoring-only mode).

**`duration`** — how long the IP stays blocked. Set to `null` for a permanent block.

**`log_level`** — Laravel log level used when recording this alert. Accepts any standard Laravel log level: `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`.

## Traps

Each trap can be individually enabled, given a custom path, and configured with a `login_success_behavior`:

```php
'traps' => [
    'wordpress' => [
        'enabled' => env('NOT_TODAY_HONEY_WP_ENABLED', true),
        'path' => env('NOT_TODAY_HONEY_WP_PATH', '/wp-admin'),
        'login_success_behavior' => TrapBehavior::from(env('NOT_TODAY_HONEY_WP_LOGIN_SUCCESS_BEHAVIOR', 'fake_success')),
        'specific' => [
            'version' => env('NOT_TODAY_HONEY_WP_VERSION', '6.4.2'),
        ],
    ],
    // phpmyadmin and generic_admin follow the same structure
],
```

See [Traps](/traps) for per-trap details and behavior options.

## Storage

```php
'storage' => [
    'connection' => env('NOT_TODAY_HONEY_DB_CONNECTION', null),
    'tables' => [
        'attacker_detections' => env('NOT_TODAY_HONEY_TABLE_ATTACKER_DETECTIONS', 'nt_honey_attacker_detections'),
        'trap_attempts'       => env('NOT_TODAY_HONEY_TABLE_TRAP_ATTEMPTS', 'nt_honey_trap_attempts'),
        'credential_attempts' => env('NOT_TODAY_HONEY_TABLE_CREDENTIAL_ATTEMPTS', 'nt_honey_credential_attempts'),
    ],
],
```

**`connection`** — database connection to use. Defaults to `null`, which uses the application's default connection. Set this if you want to isolate honeypot data on a separate database.

**`tables`** — table names for each of the three storage models. Override them if your project has a table prefix or naming convention:

```env
NOT_TODAY_HONEY_DB_CONNECTION=security
NOT_TODAY_HONEY_TABLE_ATTACKER_DETECTIONS=nt_honey_attacker_detections
NOT_TODAY_HONEY_TABLE_TRAP_ATTEMPTS=nt_honey_trap_attempts
NOT_TODAY_HONEY_TABLE_CREDENTIAL_ATTEMPTS=nt_honey_credential_attempts
```
