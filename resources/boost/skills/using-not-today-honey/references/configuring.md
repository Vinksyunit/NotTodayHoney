# Configuring NotTodayHoney

All settings live in `config/not-today-honey.php` and are overridable via env.

## Traps

Three traps are enabled by default: `wordpress` (`/wp-admin`), `phpmyadmin` (`/phpmyadmin`), `generic_admin` (`/admin`). Before production, disable any trap whose default path collides with a real route.

@verbatim
<code-snippet name=".env trap overrides" lang="env">
# Disable a trap whose path you already use for a real feature
NOT_TODAY_HONEY_GENERIC_ENABLED=false

# Move a trap to a non-default path
NOT_TODAY_HONEY_WP_PATH=/wp-admin2

# Response after a fake login: fake_success (default) | 403 | 500
NOT_TODAY_HONEY_WP_LOGIN_SUCCESS_BEHAVIOR=fake_success
</code-snippet>
@endverbatim

`TrapBehavior` values in PHP:

@verbatim
<code-snippet name="TrapBehavior enum" lang="php">
use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;

TrapBehavior::FAKE_SUCCESS; // empty dashboard
TrapBehavior::FORBIDDEN;    // 403
TrapBehavior::ERROR;        // 500
</code-snippet>
@endverbatim

## Whitelist

Whitelisted IPs still trigger events but are never blocked. Use for developers, CI, and monitoring probes.

@verbatim
<code-snippet name="Whitelist IPs" lang="env">
NOT_TODAY_HONEY_WHITELIST=127.0.0.1,10.0.0.5
</code-snippet>
@endverbatim

## Alert thresholds

Each level (`probing`, `intrusion_attempt`, `attacking`) has a threshold, rolling time window (minutes), block duration (minutes), and log level. Defaults are sensible; only tune when you have data.

@verbatim
<code-snippet name="Threshold overrides" lang="env">
NOT_TODAY_HONEY_PROBING_THRESHOLD=3         # hits before Probing fires
NOT_TODAY_HONEY_PROBING_DURATION=20         # block for 20 minutes
NOT_TODAY_HONEY_ATTACKING_DURATION=43200    # 30 days for confirmed attacker
</code-snippet>
@endverbatim

## Leaked-credential detection

The `Attacking` level fires when an attacker submits a password from the watchlist.

@verbatim
<code-snippet name="Set up custom password watchlist" lang="bash">
# 1. Generate the salt (writes to .env — run ONCE)
php artisan honey:generate-salt

# 2. Hash old/leaked passwords
php artisan honey:hash-password 'oldPasswordNoLongerUsed'

# 3. Append hashes (comma-separated) to .env:
#    NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST=7f4a2b1c,3d9e1f2a
</code-snippet>
@endverbatim

Setting `NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST` disables the built-in default list. Rotating the salt invalidates every existing hash.

## Rate limiting

Per-IP and global rate limits protect the traps themselves. Global overflow dispatches `TrapCampaignDetectedEvent`. Defaults: per-IP 30/min, global 200/min. Override via `NOT_TODAY_HONEY_RATE_{IP,GLOBAL}_{ENABLED,MAX,DECAY}`.

## Storage

Tables: `nt_honey_attacker_detections`, `nt_honey_trap_attempts`, `nt_honey_credential_attempts`. Rename via `NOT_TODAY_HONEY_TABLE_*`; use a dedicated connection via `NOT_TODAY_HONEY_DB_CONNECTION`.
