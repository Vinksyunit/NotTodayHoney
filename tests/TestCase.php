<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
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
        config()->set('not-today-honey.traps.wordpress.behavior', \Vinksyunit\NotTodayHoney\Enums\TrapBehavior::FORBIDDEN);
        config()->set('not-today-honey.traps.phpmyadmin.behavior', \Vinksyunit\NotTodayHoney\Enums\TrapBehavior::FORBIDDEN);
        config()->set('not-today-honey.traps.generic_admin.behavior', \Vinksyunit\NotTodayHoney\Enums\TrapBehavior::FORBIDDEN);
    }

    protected function defineDatabaseMigrations(): void
    {
        foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
    }
}
