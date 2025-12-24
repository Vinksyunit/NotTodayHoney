# NotTodayHoney

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vinksyunit/not-today-honey.svg?style=flat-square)](https://packagist.org/packages/vinksyunit/not-today-honey)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/Vinksyunit/NotTodayHoney/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/Vinksyunit/NotTodayHoney/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/Vinksyunit/NotTodayHoney/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/Vinksyunit/NotTodayHoney/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vinksyunit/not-today-honey.svg?style=flat-square)](https://packagist.org/packages/vinksyunit/not-today-honey)

NotTodayHoney is a Laravel honeypot package designed to detect and track potential attackers by simulating attractive targets such as fake WordPress admin pages, phpmyadmin interfaces, and other commonly targeted endpoints.

## Features

- 🍯 Simulate common attack targets (wp-admin, phpmyadmin, etc.)
- 🚨 Detect and log attacker behavior
- 🔒 Configurable honeypot routes and responses
- 📊 Track attack patterns and IP addresses
- 🎭 Realistic fake login pages
- ⚡ Easy integration with existing Laravel applications

## Installation

You can install the package via composer:

```bash
composer require vinksyunit/not-today-honey
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="not-today-honey-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="not-today-honey-config"
```

This is the contents of the published config file:

```php
return [
    // Configuration options will be added here
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="not-today-honey-views"
```

## Usage

```php
use Vinksyunit\NotTodayHoney\NotTodayHoney;

// Example usage will be documented here
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Vinksyunit](https://github.com/Vinksyunit)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
