<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use Vinksyunit\NotTodayHoney\Enums\TrapBehavior;
use Vinksyunit\NotTodayHoney\NotTodayHoneyServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Vinksyunit\\NotTodayHoney\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            NotTodayHoneyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('not-today-honey.whitelist', []);
        config()->set('not-today-honey.traps.wordpress.enabled', true);
        config()->set('not-today-honey.traps.phpmyadmin.enabled', true);
        config()->set('not-today-honey.traps.generic_admin.enabled', true);
        config()->set('not-today-honey.traps.wordpress.login_success_behavior', TrapBehavior::FORBIDDEN);
        config()->set('not-today-honey.traps.phpmyadmin.login_success_behavior', TrapBehavior::FORBIDDEN);
        config()->set('not-today-honey.traps.generic_admin.login_success_behavior', TrapBehavior::FORBIDDEN);
        config()->set('not-today-honey.traps.wordpress.specific.fingerprint.enabled', true);
        config()->set('not-today-honey.traps.wordpress.specific.fingerprint.rest_api', true);
        config()->set('not-today-honey.traps.wordpress.specific.fingerprint.fake_users', ['admin', 'editor']);
        config()->set('not-today-honey.traps.wordpress.specific.fingerprint.plugins', ['really-simple-ssl' => '9.1.1']);
        config()->set('not-today-honey.traps.phpmyadmin.specific.fingerprint.enabled', true);
        config()->set('not-today-honey.traps.phpmyadmin.specific.fingerprint.lang', 'en');
        config()->set('not-today-honey.timing.min_response_ms', 0);
        config()->set('not-today-honey.rate_limiting.per_ip.enabled', false);
        config()->set('not-today-honey.rate_limiting.global.enabled', false);
        config()->set('not-today-honey.credentials.passwords.include_defaults', true);
    }

    protected function defineDatabaseMigrations(): void
    {
        foreach (File::allFiles(__DIR__.'/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
    }
}
