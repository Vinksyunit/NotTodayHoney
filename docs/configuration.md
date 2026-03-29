# Configuration

The published config file lives at `config/not-today-honey.php`.

## Whitelist

IPs that are never blocked. They still trigger events with `is_test = true`, so you can test listeners safely.

```php
'whitelist' => explode(',', env('NOT_TODAY_HONEY_WHITELIST', '127.0.0.1')),
```

Set `NOT_TODAY_HONEY_WHITELIST=127.0.0.1,10.0.0.1` in `.env` for multiple IPs.

## Credentials

Known usernames and bcrypt password hashes. When a submitted password matches a hash in this list, the alert level is automatically escalated to **Attacking**.

```php
'credentials' => [
    'usernames' => explode(',', env('NOT_TODAY_HONEY_USERNAMES', 'admin,administrator,webmaster,root,maintenance')),
    'passwords' => [
        [
            'id' => 'rockyou_top_1',
            'hash' => env('NOT_TODAY_HONEY_HASH_1', '...'), // bcrypt of "password"
        ],
        [
            'id' => 'common_bot_pass',
            'hash' => env('NOT_TODAY_HONEY_HASH_2', '...'), // bcrypt of "123456"
        ],
    ],
],
```

Add your own hashes by generating them with `php artisan tinker`:
```php
bcrypt('yourpassword');
```

Store hashes in `.env` — never commit them in plain text.

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
NOT_TODAY_HONEY_TABLE_ATTACKER_DETECTIONS=honey_attackers
NOT_TODAY_HONEY_TABLE_TRAP_ATTEMPTS=honey_traps
NOT_TODAY_HONEY_TABLE_CREDENTIAL_ATTEMPTS=honey_credentials
```
