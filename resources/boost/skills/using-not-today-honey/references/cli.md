# NotTodayHoney Artisan Commands

## `honey:status` — list blocked IPs

@verbatim
<code-snippet name="Status" lang="bash">
php artisan honey:status
</code-snippet>
@endverbatim

Prints a table of IP, alert level, attempt count, and block expiry. `No IPs are currently blocked.` when empty.

## `honey:unblock {ip}` — lift a block

@verbatim
<code-snippet name="Unblock an IP" lang="bash">
php artisan honey:unblock 1.2.3.4
</code-snippet>
@endverbatim

Wipes every detection record for that IP. If the IP is not blocked, exits successfully with a notice.

## `honey:generate-salt` — one-time salt setup

@verbatim
<code-snippet name="Generate salt" lang="bash">
php artisan honey:generate-salt
</code-snippet>
@endverbatim

Generates a random 32-char salt and writes/updates `NOT_TODAY_HONEY_PASSWORD_SALT` in `.env`. Run **once** per environment. Rotating invalidates every existing hash.

## `honey:hash-password {password}` — add to watchlist

@verbatim
<code-snippet name="Hash a password" lang="bash">
php artisan honey:hash-password 'oldPasswordNoLongerUsed'
</code-snippet>
@endverbatim

Outputs an 8-char truncated SHA256. Append to `NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST` (comma-separated).

## Typical workflows

**Set up credential detection:**
```
php artisan honey:generate-salt
php artisan honey:hash-password 'letmein'
php artisan honey:hash-password 'Password123'
# Merge outputs into NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST, then restart the app
```

**Investigate a block complaint:**
```
php artisan honey:status                 # confirm the IP is listed
php artisan honey:unblock 203.0.113.42   # lift the block after verification
```

## Guidance

- Never hash a password currently in use anywhere. Use historical, leaked, or throwaway credentials only.
- `honey:unblock` resets the IP fully. Repeated unblocks of the same IP usually indicate a misconfigured whitelist.
- For programmatic equivalents of these commands, see `middleware-and-facade.md`.
