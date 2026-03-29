# Trap Behavior — Login Success Redesign

**Date:** 2026-03-29
**Status:** Approved

## Problem

`TrapBehavior` (FORBIDDEN, ERROR, INFINITE_LOADING, FAKE_SUCCESS) is currently applied on every interaction with a honeypot trap — including GET requests (initial page visit). This defeats the honeypot's purpose: a bot visiting `/wp-login.php` that immediately receives a 403 learns nothing realistic. The config key `behavior` also gives no hint about when the behavior fires.

## Intent

`TrapBehavior` is the simulated result of a fake successful login. It is what the attacker sees after submitting credentials that the honeypot accepts as valid (i.e., a password matching a known leaked hash). It is not the response to probing visits or failed login attempts.

## Corrected Flow

| Interaction | Response |
|---|---|
| GET (probe/visit) | Always render the realistic fake login page |
| POST — password does **not** match known hash | Render realistic trap-specific "wrong credentials" error HTML |
| POST — password **matches** known hash | Apply `login_success_behavior` (`TrapBehavior`) |

## Changes

### 1. Behavior routing in `HandlesTrapBehavior` trait

**`executeTrap()` (GET handler):**
- Remove the call to `respondWithBehavior()`.
- Always return the realistic login page view.
- PROBING detection recording is unchanged.

**`executeLoginTrap()` (POST handler):**
- When `password_matched === false`: return realistic trap-specific error HTML (no change to this path).
- When `password_matched === true`: call `respondWithBehavior($loginSuccessBehavior)` — the only place `TrapBehavior` is applied.

### 2. Config key rename

In `config/not-today-honey.php`, rename `behavior` to `login_success_behavior` for every trap entry:

```php
'traps' => [
    'wordpress' => [
        'enabled' => true,
        'path' => '/wp-admin',
        'login_success_behavior' => TrapBehavior::FAKE_SUCCESS,
        'specific' => ['version' => '6.4.2'],
    ],
    'phpmyadmin' => [
        'enabled' => true,
        'path' => '/phpmyadmin',
        'login_success_behavior' => TrapBehavior::FAKE_SUCCESS,
        'specific' => ['pma_version' => '5.2.1'],
    ],
    'generic_admin' => [
        'enabled' => true,
        'path' => '/admin',
        'login_success_behavior' => TrapBehavior::FAKE_SUCCESS,
        'specific' => ['title' => 'Control Panel'],
    ],
],
```

**Default changes from `TrapBehavior::FORBIDDEN` to `TrapBehavior::FAKE_SUCCESS`.**
Rationale: post-success, showing a fake dashboard is the semantically correct default. FORBIDDEN was reasonable when the behavior fired on every visit; after a fake login it would be confusing.

All internal config reads are updated from `...traps.{name}.behavior` to `...traps.{name}.login_success_behavior`.

### 3. Test updates

- **GET tests**: update assertions from behavior response (e.g., 403) to realistic login page HTML/status 200.
- **POST wrong-credentials tests**: no change needed.
- **POST matching-credentials tests**: confirm `TrapBehavior` fires only on password match, not on every POST.
- **Config fixtures/factories**: update any reference to the `behavior` key to `login_success_behavior`.

## Out of Scope

- Changes to alert level logic (`PROBING`, `INTRUSION_ATTEMPT`, `ATTACKING`) — unchanged.
- Changes to blocking thresholds, durations, or notification channels — unchanged.
- New trap types — unchanged.
