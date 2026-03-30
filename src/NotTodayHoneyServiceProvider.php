<?php

declare(strict_types=1);

namespace Vinksyunit\NotTodayHoney;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vinksyunit\NotTodayHoney\Commands\HoneyGenerateSaltCommand;
use Vinksyunit\NotTodayHoney\Commands\HoneyHashPasswordCommand;
use Vinksyunit\NotTodayHoney\Commands\HoneyStatusCommand;
use Vinksyunit\NotTodayHoney\Commands\HoneyUnblockCommand;
use Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotBlockMiddleware;
use Vinksyunit\NotTodayHoney\Http\Middleware\HoneypotRateLimitMiddleware;
use Vinksyunit\NotTodayHoney\Services\AttackerDetectionService;

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
                HoneyHashPasswordCommand::class,
                HoneyGenerateSaltCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $this->app['router']->aliasMiddleware(
            'honeypot.block',
            HoneypotBlockMiddleware::class
        );

        $this->app['router']->aliasMiddleware(
            'honeypot.rate_limit',
            HoneypotRateLimitMiddleware::class
        );

        $this->loadRoutesFrom(__DIR__.'/../routes/traps.php');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(AttackerDetectionService::class);
        $this->app->bind(
            NotTodayHoney::class,
            fn ($app): \Vinksyunit\NotTodayHoney\NotTodayHoney => new NotTodayHoney(
                $app->make(AttackerDetectionService::class)
            )
        );
    }
}
