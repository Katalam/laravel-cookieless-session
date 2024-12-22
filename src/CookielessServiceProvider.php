<?php

declare(strict_types=1);

namespace Katalam\Cookieless;

use Illuminate\Support\Facades\Blade;
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
            ->hasConfigFile();
    }

    public function bootingPackage(): void
    {
        Blade::directive('sessionToken', static function () {
            return '<?php echo session_field(); ?>';
        });
    }
}
