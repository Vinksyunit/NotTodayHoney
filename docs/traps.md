# Traps

A trap is a realistic-looking login page served at a URL that legitimate users would never visit. When a scanner or attacker hits the URL, NotTodayHoney records the event and classifies it.

## WordPress Trap

Simulates the WordPress `wp-login.php` login page, including the spoofed version number in the HTML.

**Default URL:** `/wp-login.php`

**Environment variables:**

| Variable | Default | Purpose |
|----------|---------|---------|
| `NOT_TODAY_HONEY_WP_ENABLED` | `true` | Enable/disable the trap |
| `NOT_TODAY_HONEY_WP_PATH` | `/wp-admin` | Mount path |
| `NOT_TODAY_HONEY_WP_LOGIN_SUCCESS_BEHAVIOR` | `fake_success` | Response after a login attempt |
| `NOT_TODAY_HONEY_WP_VERSION` | `6.4.2` | Spoofed WP version shown in the page |

## phpMyAdmin Trap

Simulates the phpMyAdmin login screen.

**Default URL:** `/phpmyadmin`

**Environment variables:**

| Variable | Default | Purpose |
|----------|---------|---------|
| `NOT_TODAY_HONEY_PMA_ENABLED` | `true` | Enable/disable the trap |
| `NOT_TODAY_HONEY_PMA_PATH` | `/phpmyadmin` | Mount path |
| `NOT_TODAY_HONEY_PMA_LOGIN_SUCCESS_BEHAVIOR` | `fake_success` | Response after a login attempt |
| `NOT_TODAY_HONEY_PMA_VERSION` | `5.2.1` | Spoofed PMA version shown in the page |

## Generic Admin Trap

A generic control panel login page. The title is configurable to match whatever your attacker is scanning for.

**Default URL:** `/admin/login`

**Environment variables:**

| Variable | Default | Purpose |
|----------|---------|---------|
| `NOT_TODAY_HONEY_GENERIC_ENABLED` | `true` | Enable/disable the trap |
| `NOT_TODAY_HONEY_GENERIC_PATH` | `/admin` | Mount path (login served at `{path}/login`) |
| `NOT_TODAY_HONEY_GENERIC_LOGIN_SUCCESS_BEHAVIOR` | `fake_success` | Response after a login attempt |
| `NOT_TODAY_HONEY_GENERIC_TITLE` | `Control Panel` | Page title shown in the login form |

## Login Success Behaviors

When an attacker submits the login form, the `login_success_behavior` value controls what they see:

| Value | HTTP Status | Effect |
|-------|-------------|--------|
| `fake_success` | 200 | Renders an empty dashboard — attacker thinks they're in |
| `403` | 403 Forbidden | Standard access denied response |
| `500` | 500 Internal Server Error | Simulates a server crash |
| `infinite_loading` | — | Tarpitting: the response hangs until the request times out |

Configure per trap in `.env`:
```env
NOT_TODAY_HONEY_WP_LOGIN_SUCCESS_BEHAVIOR=fake_success
NOT_TODAY_HONEY_PMA_LOGIN_SUCCESS_BEHAVIOR=403
NOT_TODAY_HONEY_GENERIC_LOGIN_SUCCESS_BEHAVIOR=infinite_loading
```
