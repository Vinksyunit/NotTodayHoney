<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vinksyunit\NotTodayHoney\Commands\HoneyStatusCommand;
use Vinksyunit\NotTodayHoney\Commands\HoneyUnblockCommand;
use Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotBlockMiddleware;

class NotTodayHoneyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('not-today-honey')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_nt_honey_attacker_detections_table')
            ->hasMigration('create_nt_honey_trap_attempts_table')
            ->hasMigration('create_nt_honey_credential_attempts_table')
            ->hasCommands([
                HoneyStatusCommand::class,
                HoneyUnblockCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $this->app['router']->aliasMiddleware(
            'honeypot.block',
            HoneypotBlockMiddleware::class
        );

        $this->loadRoutesFrom(__DIR__.'/../routes/traps.php');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(\Vinksyunit\NotTodayHoney\Services\AttackerDetectionService::class);
    }
}
