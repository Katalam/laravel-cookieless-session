<?php

declare(strict_types=1);

namespace Katalam\Cookieless\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Katalam\Cookieless\CookielessServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Katalam\\Cookieless\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            CookielessServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-cookieless-session_table.php.stub';
        $migration->up();
        */
    }
}
