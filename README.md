<p align="center">
  <img src="docs/public/logo.svg" width="200" alt="NotTodayHoney">
</p>

<p align="center">
  <a href="https://packagist.org/packages/vinksyunit/not-today-honey"><img src="https://img.shields.io/packagist/v/vinksyunit/not-today-honey.svg?style=flat-square" alt="Latest Version on Packagist"></a>
  <a href="https://github.com/Vinksyunit/NotTodayHoney/actions?query=workflow%3Arun-tests+branch%3Amain"><img src="https://img.shields.io/github/actions/workflow/status/Vinksyunit/NotTodayHoney/run-tests.yml?branch=main&label=tests&style=flat-square" alt="Tests"></a>
  <a href="https://github.com/Vinksyunit/NotTodayHoney/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain"><img src="https://img.shields.io/github/actions/workflow/status/Vinksyunit/NotTodayHoney/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square" alt="Code Style"></a>
  <a href="https://packagist.org/packages/vinksyunit/not-today-honey"><img src="https://img.shields.io/packagist/dt/vinksyunit/not-today-honey.svg?style=flat-square" alt="Total Downloads"></a>
  <img src="https://img.shields.io/packagist/php-v/vinksyunit/not-today-honey?logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel" alt="Laravel Version">
  <img src="https://img.shields.io/packagist/l/vinksyunit/not-today-honey" alt="License">
</p>

A Laravel honeypot package that simulates realistic admin pages (WordPress, phpMyAdmin) to detect and block attackers.

## Detect threats, automatically

- **3-level alert system** — Probing → Intrusion Attempt → Attacking, each with configurable thresholds, block durations, and log levels
- **Leaked credential detection** — truncated SHA256 comparison against known password lists; immediate escalation to Attacking on match

## Protect your real features

- **Automatic IP blocking** — detected attackers are blocked for configurable durations (minutes for probing, days for intrusion, weeks for attacking)
- **`nottodayhoney.block` middleware** — deny blocked IPs globally or per route group with a single line

## Honeypot traps that fool scanners

- **Realistic decoys** — fake WordPress wp-login, phpMyAdmin, and generic admin pages with HTTP fingerprinting to attract CVE scanners and credential-stuffing bots
- **Event-driven alerts** — Laravel events at each alert level; wire up Slack, mail, or any channel via listeners

## Requirements

- PHP 8.4+
- Laravel 12+

## Installation

```bash
composer require vinksyunit/not-today-honey
php artisan vendor:publish --tag="not-today-honey-config"
php artisan migrate
```

→ [Full documentation](https://vinksyunit.github.io/NotTodayHoney)

## Sponsors

### Special Sponsors

<p align="center">
  <a href="https://starkado.com/" target="_blank" rel="noopener">
    <span style="display:inline-block;background:#ffffff;border-radius:8px;padding:12px 20px">
      <img src="https://starkado.com/images/landing/logo-starkado.svg" alt="Starkado" width="160">
    </span>
  </a>
</p>

## Blue team best practices

NotTodayHoney detects and signals — it is one layer of a defense-in-depth strategy. A honeypot without complementary layers is a smoke detector with no sprinklers.

- **Understand your attack surface** — the [OWASP Top 10](https://owasp.org/www-project-top-10/) covers the most common application-layer risks; the [ASVS](https://owasp.org/www-project-application-security-verification-standard/) gives you a structured checklist
- **Review code for security** — authentication, authorisation boundaries, and input handling deserve attention on every change, not just security-focused sprints
- **Run penetration tests** — a pentest finds what automated scanners miss: logic flaws, misconfigurations, privilege escalation paths
- **Monitor and respond** — route `AttackerAttackingEvent` to an alerting pipeline; define a runbook for what your team does when an attacker is detected
- **Keep dependencies clean** — attackers scan for known CVEs before trying credentials; run `composer audit` regularly

→ [Blue Team Practices](https://vinksyunit.github.io/NotTodayHoney/blue-team) in the documentation for further reading and OWASP references.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
