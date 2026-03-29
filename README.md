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

## Features

- Realistic honeypot traps — fake WordPress wp-login, phpMyAdmin, and generic `/admin` pages
- 3-level alert system: **Probing** → **Intrusion Attempt** → **Attacking**
- Leaked credential detection via bcrypt hash comparison against known password lists
- Automatic IP blocking with configurable durations per alert level
- Event-driven architecture — wire up Slack, mail, or log notifications via listeners
- `honeypot.block` middleware to protect any route from blocked IPs

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

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
