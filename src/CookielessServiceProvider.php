<?php

declare(strict_types=1);

namespace Katalam\Cookieless;

use Katalam\Cookieless\Commands\CookielessCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CookielessServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-cookieless-session')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_cookieless_session_table')
            ->hasCommand(CookielessCommand::class);
    }
}
