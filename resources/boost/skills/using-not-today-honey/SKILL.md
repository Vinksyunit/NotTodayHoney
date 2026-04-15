---
name: using-not-today-honey
description: Install, configure, and operate NotTodayHoney — a Laravel honeypot package that exposes fake admin pages (wp-admin, phpmyadmin, admin) to detect and auto-block attackers. Covers configuration, event listeners, blocking middleware, facade API, and artisan commands.
---

# Using NotTodayHoney

NotTodayHoney exposes fake admin pages to attract scanners and credential-stuffing bots, then escalates through three alert levels — Probing → Intrusion Attempt → Attacking — and auto-blocks detected IPs.

## Non-negotiable rules

- Trap paths must NOT collide with real application routes. Disable any trap whose default path the app already serves (env: `NOT_TODAY_HONEY_{WP,PMA,GENERIC}_ENABLED=false`).
- Never apply auth, CSRF, or rate-limit middleware to the package's own trap routes.
- `NOT_TODAY_HONEY_PASSWORD_SALT` must stay secret and stable. Rotating it invalidates every custom password hash.
- Never hash a password currently in use anywhere. Watchlist entries must be old, leaked, or throwaway credentials.

## Quick install

@verbatim
<code-snippet name="Install and publish" lang="bash">
composer require vinksyunit/not-today-honey
php artisan vendor:publish --tag="not-today-honey-config"
php artisan migrate
</code-snippet>
@endverbatim

## Load the reference for the task at hand

Read the file that matches the user's request, not all of them:

- Configuring trap paths, whitelist, thresholds, or leaked-credential detection → `references/configuring.md`
- Listening to attacker/campaign events → `references/events.md`
- Applying the blocking middleware or querying block state in code → `references/middleware-and-facade.md`
- Running artisan commands (status, unblock, salt, hash-password) → `references/cli.md`

## Key types at a glance

- Facade: `Vinksyunit\NotTodayHoney\Facades\NotTodayHoney` (or inject the class `Vinksyunit\NotTodayHoney\NotTodayHoney`).
- Enums: `Vinksyunit\NotTodayHoney\Enums\AlertLevel` (`PROBING`, `INTRUSION_ATTEMPT`, `ATTACKING`), `Vinksyunit\NotTodayHoney\Enums\TrapBehavior` (`FAKE_SUCCESS`, `FORBIDDEN`, `ERROR`).
- Contract: `Vinksyunit\NotTodayHoney\Contracts\AttackerAlertEvent`.
- Model: `Vinksyunit\NotTodayHoney\Models\AttackerDetection` (attributes: `ip`, `alert_level`, `attempt_count`, `blocked_until`).
- Middleware alias: `nottodayhoney.block`.
