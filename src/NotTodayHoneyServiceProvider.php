<?php

namespace Vinksyunit\NotTodayHoney;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vinksyunit\NotTodayHoney\Commands\NotTodayHoneyCommand;

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
            ->hasMigration('create_not_today_honey_table')
            ->hasCommand(NotTodayHoneyCommand::class);
    }
}
