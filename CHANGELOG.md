# Changelog

All notable changes to `NotTodayHoney` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/), and this project adheres to [Semantic Versioning](https://semver.org/).

## v0.1.0 - Initial Release - 2026-04-14

### 🎉 Initial Release

First public release of **NotTodayHoney** — a Laravel honeypot package that detects and blocks malicious bots and attackers.

#### Added

- **Honeypot traps**: WordPress wp-login, phpMyAdmin, and Generic admin panel
- **HTTP fingerprinting**: WP REST API users endpoint, vulnerable plugin readme.txt, phpMyAdmin session cookies
- **3-level alert system** (Probing, Intrusion Attempt, Attacking) with configurable thresholds and block durations
- **Laravel events**: `ProbeEvent`, `IntrusionAttemptEvent`, `AttackingEvent`, `TrapCampaignDetectedEvent`
- **Leaked credential detection** via truncated SHA256 comparison
- **`nottodayhoney.block` middleware** for IP blocking
- **Per-IP and global rate limiting** with campaign detection
- **Timing normalization** (Timebox-based minimum response time)
- **Artisan commands**: `honey:hash-password` and `honey:generate-salt`
- **IP whitelist support**
- **VitePress documentation site** with guides

#### Fixed

- Whitelisted IPs bypass rate limiting
- `TrapCampaignDetectedEvent` dispatched only on first global limit breach

## 0.1.0 - 2026-04-14

### Added

- Honeypot traps: WordPress wp-login, phpMyAdmin, and Generic admin panel
- HTTP fingerprinting (WP REST API users endpoint, vulnerable plugin readme.txt, phpMyAdmin session cookies)
- 3-level alert system (Probing, Intrusion Attempt, Attacking) with configurable thresholds and block durations
- Laravel events: `ProbeEvent`, `IntrusionAttemptEvent`, `AttackingEvent`, `TrapCampaignDetectedEvent`
- Leaked credential detection via truncated SHA256 comparison
- `nottodayhoney.block` middleware for IP blocking
- Per-IP and global rate limiting with campaign detection
- Timing normalization (Timebox-based minimum response time)
- `honey:hash-password` and `honey:generate-salt` Artisan commands
- IP whitelist support
- VitePress documentation site with guides

### Fixed

- Whitelisted IPs bypass rate limiting
- `TrapCampaignDetectedEvent` dispatched only on first global limit breach
