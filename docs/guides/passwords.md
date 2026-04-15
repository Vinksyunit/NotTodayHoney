# Compromised Passwords

Automated credential-stuffing attacks use known leaked password lists — the same lists from major data breaches like RockYou. When an attacker tries a password your honeypot recognises, the alert escalates immediately to **Attacking**, the highest severity level.

This guide explains how the detection works and how to configure it.

## Why truncated SHA256 instead of bcrypt

The naive approach would be to store bcrypt hashes and run `password_verify()` for each submitted password. The problem: bcrypt is intentionally slow. Checking every password in your list on every login attempt would add hundreds of milliseconds to every trap response — creating a timing oracle that reveals how many hashes you have.

NotTodayHoney uses a different approach:

1. The submitted password is combined with a **salt** and hashed with SHA256
2. Only the **first 8 hex characters** of the hash are kept
3. That 8-character string is compared against your list

The comparison is fast and constant-time. The salt makes rainbow tables useless. Truncation reduces the risk of the list itself being useful to an attacker if `.env` is ever exposed.

## Setup: generate a salt

Run this once per application:

```bash
php artisan honey:generate-salt
```

Output:
```
NOT_TODAY_HONEY_PASSWORD_SALT=a3f8c2e1d9b74056
```

Add the output line to your `.env`. Do not commit it.

::: warning
If you change the salt, all existing custom hashes become invalid and must be regenerated.
:::

## Setup: hash your passwords

Add a password to your detection list:

```bash
php artisan honey:hash-password "password123"
# NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST=7f4a2b1c
```

Add multiple passwords and concatenate their hashes:

```env
NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST=7f4a2b1c,3d9e1f2a,c8b05a44
```

Once this variable is set, the built-in default list is automatically disabled (`include_defaults` becomes `false`).

## The built-in default list

When `NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST` is not set, a small built-in list of very common passwords is active. This gives you basic detection out of the box without any setup.

For production use, replace it with a custom list that matches the passwords attackers actually try against your stack. Public resources like [SecLists](https://github.com/danielmiessler/SecLists/tree/master/Passwords) provide curated credential lists.

## Usernames

Detection only escalates when both a recognised username **and** a recognised password are submitted together. Configure the username list:

```env
NOT_TODAY_HONEY_USERNAMES=admin,administrator,webmaster,root,maintenance
```

Extend this list to match the usernames common in your application's ecosystem (e.g. `wp-admin` for WordPress-specific attacks).

## What happens on a match

When a submitted credential pair matches:

1. The alert level is set to `attacking`
2. `AttackerAttackingEvent` is dispatched
3. The IP is blocked for 30 days (default)

### Identifying which password was used

The `CredentialAttempt` record stores the `password_hash` — the 8-character truncated hash that matched. You can access it from the event to identify which entry in your list was triggered:

```php
use Vinksyunit\NotTodayHoney\Events\AttackerAttackingEvent;

Event::listen(AttackerAttackingEvent::class, function (AttackerAttackingEvent $event) {
    $attempt = $event->getDetection()->credentialAttempts()->latest()->first();

    Log::critical("Leaked credential used from {$event->getIp()}", [
        'password_hash' => $attempt?->password_hash, // matches an entry in your NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST
        'username'      => $attempt?->username_used,
    ]);
});
```

Cross-reference `password_hash` against your `NOT_TODAY_HONEY_PASSWORD_SHORT_SHA_LIST` to identify which specific password was tried. The plain-text password is never stored — only the truncated hash.

See [Configuration → Credentials](/configuration#credentials) for the full config reference.
