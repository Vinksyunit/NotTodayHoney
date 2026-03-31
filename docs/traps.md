# Traps

A trap is a realistic-looking login page served at a URL that legitimate users would never visit. When a scanner or attacker hits the URL, NotTodayHoney records the event and classifies it.

## WordPress Trap

Simulates the WordPress `wp-login.php` login page, including the spoofed version number in the HTML.

**Route:** `GET /wp-login.php`, `POST /wp-login.php`

**Environment variables:**

| Variable | Default | Purpose |
|----------|---------|---------|
| `NOT_TODAY_HONEY_WP_ENABLED` | `true` | Enable/disable the trap |
| `NOT_TODAY_HONEY_WP_PATH` | `/wp-admin` | Mount path |
| `NOT_TODAY_HONEY_WP_LOGIN_SUCCESS_BEHAVIOR` | `fake_success` | Response after a login attempt |
| `NOT_TODAY_HONEY_WP_VERSION` | `6.4.2` | Spoofed WP version shown in the page |
| `NOT_TODAY_HONEY_WP_SITE_NAME` | `WordPress` | Site name shown in the login page title |
| `NOT_TODAY_HONEY_WP_LOGO_URL` | *(none)* | URL of a custom logo to display |
| `NOT_TODAY_HONEY_WP_MIN_RESPONSE_MS` | *(global)* | Per-trap minimum response time override |

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

Configure per trap in `.env`:
```env
NOT_TODAY_HONEY_WP_LOGIN_SUCCESS_BEHAVIOR=fake_success
NOT_TODAY_HONEY_PMA_LOGIN_SUCCESS_BEHAVIOR=403
NOT_TODAY_HONEY_GENERIC_LOGIN_SUCCESS_BEHAVIOR=500
```

## Fingerprinting

Fingerprinting makes traps behave like real software at the HTTP level. When enabled, responses include the headers, cookies, and endpoints that scanners expect to find — causing automated tools to invest more effort before determining the target is genuine.

### WordPress Fingerprinting

When enabled (`NOT_TODAY_HONEY_WP_FINGERPRINT_ENABLED=true`), the WordPress trap adds:

- **Response headers** — `X-Powered-By: PHP/x.x.x` spoofed to the configured PHP version
- **REST API discovery** — `GET /wp-json/` returns a discovery document listing available namespaces
- **User enumeration endpoint** — `GET /wp-json/wp/v2/users` returns a fake user list (configured via `NOT_TODAY_HONEY_WP_FINGERPRINT_FAKE_USERS`)
- **Vulnerable plugin endpoint** — `GET {path}/wp-content/plugins/{plugin}/readme.txt` returns a fake readme that advertises a vulnerable version, attracting CVE scanners

| Variable | Default | Purpose |
|----------|---------|---------|
| `NOT_TODAY_HONEY_WP_FINGERPRINT_ENABLED` | `true` | Enable WordPress fingerprinting |
| `NOT_TODAY_HONEY_WP_FINGERPRINT_PHP_VERSION` | `8.1.0` | PHP version advertised in headers |
| `NOT_TODAY_HONEY_WP_FINGERPRINT_REST_API` | `true` | Enable REST API endpoints |
| `NOT_TODAY_HONEY_WP_FINGERPRINT_FAKE_USERS` | `admin` | Comma-separated list of fake usernames exposed via REST API |

### phpMyAdmin Fingerprinting

When enabled (`NOT_TODAY_HONEY_PMA_FINGERPRINT_ENABLED=true`), the phpMyAdmin trap sets a `phpMyAdmin` session cookie on the login page response — the same cookie a real phpMyAdmin instance sets before authentication.

| Variable | Default | Purpose |
|----------|---------|---------|
| `NOT_TODAY_HONEY_PMA_FINGERPRINT_ENABLED` | `true` | Enable phpMyAdmin fingerprinting |
| `NOT_TODAY_HONEY_PMA_FINGERPRINT_LANG` | `en` | Language advertised in the session cookie |

::: tip
Fingerprinting is enabled by default for all traps. Disable it only if you need to reduce the attack surface of the honeypot itself.
:::
