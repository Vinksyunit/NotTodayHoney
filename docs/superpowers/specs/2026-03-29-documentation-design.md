# Documentation Design — NotTodayHoney

**Date:** 2026-03-29
**Status:** Approved

## Overview

Create a VitePress documentation site hosted on GitHub Pages, plus rewrite the README as a lightweight pointer page. The docs site is the canonical reference; the README covers install + quick start only.

## Decisions

| Decision | Choice | Reason |
|----------|--------|--------|
| GitHub Pages type | Full documentation site | Canonical reference for the package |
| Tech stack | VitePress | Industry standard for Laravel packages, great default dark theme |
| README role | Lightweight pointer | Full detail lives in docs, README is GitHub landing page |
| Docs depth | Lean — 5 pages | Matches current scope of the package |
| Hosting strategy | `docs/` in main repo + GitHub Actions | Automated deploys, docs travel with code |

---

## File Structure

```
docs/
  .vitepress/
    config.ts            # nav, sidebar, theme config (orange accent #f97316)
  index.md               # VitePress homepage (hero + features grid)
  getting-started.md     # install, publish config, migrate, quick start
  configuration.md       # whitelist, credentials, alert levels, traps, storage
  traps.md               # WordPress, phpMyAdmin, Generic Admin, behaviors
  events-middleware.md   # events (3 types + examples), middleware, facade API
  commands.md            # honey:status, honey:unblock

.github/
  workflows/
    docs.yml             # build VitePress → deploy to gh-pages on push to main

README.md                # rewrite: logo, badges, features, install snippet, → docs link
```

---

## README Rewrite

The README becomes a polished GitHub landing page. Content:

1. **Logo** — `Not-Today-Honey.svg` centered
2. **Badges row** — packagist version, tests CI, code style CI, total downloads, PHP version, Laravel version, MIT license
3. **One-liner** — "A Laravel honeypot package that simulates realistic admin pages (WordPress, phpMyAdmin) to detect and block attackers."
4. **Feature bullets** (6 max):
   - Realistic honeypot traps (WordPress wp-login, phpMyAdmin, generic /admin)
   - 3-level alert system: Probing → Intrusion Attempt → Attacking
   - Leaked credential detection via bcrypt hash comparison
   - Automatic IP blocking with configurable durations per level
   - Event-driven architecture (dispatch listeners for Slack, mail, log)
   - `honeypot.block` middleware to protect any route
5. **Requirements** — PHP 8.4+, Laravel 12+
6. **Install block** — `composer require`, `vendor:publish --tag="not-today-honey-config"`, `php artisan migrate`
7. **Link** — "→ Full documentation at [GitHub Pages URL]"

Note: fixes stale config example in current README (uses `behavior` instead of `login_success_behavior`).

---

## VitePress Config (`docs/.vitepress/config.ts`)

- **Site title:** NotTodayHoney
- **Description:** Laravel honeypot package to detect and block attackers
- **Theme:** Default VitePress dark theme with orange accent color (`#f97316`)
- **Nav:** Home, GitHub (→ repo URL)
- **Sidebar:** 5 items at top level (no sub-groups)
  - Getting Started
  - Configuration
  - Traps
  - Events & Middleware
  - Artisan Commands

---

## Docs Homepage (`docs/index.md`)

VitePress `layout: home` with:

- **Hero:**
  - Name: NotTodayHoney
  - Tagline: "Stop attackers before they start"
  - Text: "Laravel honeypot traps with 3-level detection, automatic IP blocking, and event-driven alerts"
  - Actions: **Get Started** (→ /getting-started), **GitHub** (→ repo, outline style)
- **Features grid (6 items):**
  - Realistic Traps — Fake WordPress, phpMyAdmin, and generic admin login pages
  - 3-Level Detection — Probing, Intrusion Attempt, and Attacking alert levels
  - Leaked Credential Detection — bcrypt hash comparison against known password lists
  - Automatic IP Blocking — Configurable block durations per alert level
  - Event-Driven Alerts — Dispatch listeners for Slack, mail, and log channels
  - Middleware Protection — Block detected attackers from any route instantly

---

## Docs Pages — Content Outline

### Getting Started (`getting-started.md`)
1. Install via Composer
2. Publish config: `php artisan vendor:publish --tag="not-today-honey-config"`
3. Run migrations: `php artisan migrate`
4. Add `honeypot.block` middleware (global example in `bootstrap/app.php`)
5. Quick test: visit `/wp-admin` — the trap is live

### Configuration (`configuration.md`)
- **Whitelist** — `NOT_TODAY_HONEY_WHITELIST` env var, default `127.0.0.1`, comma-separated
- **Credentials** — purpose (leaked password detection), how to add bcrypt hashes, `usernames` list
- **Alert Levels** — table showing threshold / time_window / mark_as_insecure / duration / log_level for each of probing, intrusion_attempt, attacking; note that log_level accepts any standard Laravel log level string (debug, info, notice, warning, error, critical, alert, emergency)
- **Traps** — per-trap options: enabled, path, `login_success_behavior`, trap-specific fields (WP version, PMA version, generic title)
- **Storage** — driver (database) and table name

### Traps (`traps.md`)
- **Overview** — what a trap is, how it detects
- **WordPress** — simulates `wp-login.php`, env vars, spoofed WP version
- **phpMyAdmin** — simulates `/phpmyadmin/`, env vars, spoofed PMA version
- **Generic Admin** — simulates `/admin/login`, env vars, configurable title
- **Login Success Behaviors** — table: `fake_success` / `403` / `500` / `infinite_loading` with description of each

### Events & Middleware (`events-middleware.md`)
- **Events** — `AttackerProbingEvent`, `AttackerIntrusionAttemptEvent`, `AttackerAttackingEvent`; when each fires; available methods (`getIp()`, `getAttemptCount()`, `getAlertLevel()`, `isTest()`); listener code example
- **Middleware** — `honeypot.block` alias; global registration; route group registration; behavior (403 if blocked, whitelist bypass)
- **Facade API** — `NotTodayHoney::isBlocked()`, `getBlockedIps()`, `unblock()`, `getDetection()`, `getDetectionsByLevel()`

### Artisan Commands (`commands.md`)
- `honey:status` — lists currently blocked IPs in a table (IP, alert level, attempts, blocked until)
- `honey:unblock {ip}` — removes all detection records for a given IP

---

## GitHub Actions Workflow (`.github/workflows/docs.yml`)

- **Trigger:** push to `main`
- **Steps:**
  1. Checkout
  2. Setup Node.js (LTS)
  3. Install dependencies (`npm ci` inside `docs/`)
  4. Build (`npm run docs:build`)
  5. Deploy `docs/.vitepress/dist` to `gh-pages` branch via `peaceiris/actions-gh-pages`
- **GitHub Pages setting:** source = `gh-pages` branch, root `/`

---

## Out of Scope

- Custom domain (can be added later via `CNAME`)
- Algolia search (can be added once the package is public)
- Versioned docs
- Changelog page
