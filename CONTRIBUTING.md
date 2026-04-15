# Contributing

Thanks for your interest in contributing to NotTodayHoney! You can report bugs, suggest features, or ask questions through [GitHub Issues & Discussions](https://github.com/Vinksyunit/NotTodayHoney/issues/new/choose).

## Setup

```bash
git clone git@github.com:Vinksyunit/NotTodayHoney.git
cd NotTodayHoney
composer install
```

## Code Quality

Before submitting a PR, run:

```bash
composer lint   # Pint (code style) + PHPStan (static analysis) + Rector (refactoring)
composer test   # Pest test suite
```

All checks must pass. New features require tests.

## Pull Requests

1. Fork the repository and create a branch from `main`
2. Make your changes -- one feature or fix per PR
3. Use [conventional commits](https://www.conventionalcommits.org/): `feat()`, `fix()`, `refactor()`, `docs()`, `chore()`
4. Run `composer lint && composer test`
5. Open a pull request against `main`

## Security

Found a vulnerability? Please see [SECURITY.md](SECURITY.md) instead of opening a public issue.
