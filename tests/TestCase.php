<?php

declare(strict_types=1);

namespace Katalam\Cookieless\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Katalam\Cookieless\CookielessServiceProvider;
use Katalam\Cookieless\Http\Middleware\StartSession;
use Katalam\Cookieless\Tests\Fixtures\VerifyCsrfToken;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as Orchestra;

#[WithMigration('laravel')]
#[WithMigration('session')]
class TestCase extends Orchestra
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            static fn (string $modelName) => 'Katalam\\Cookieless\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            CookielessServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:vXoiex6Qc5+ues0vlPYf2IOQi1ytyQ32T7R84vyAtZQ=');
        config()->set('session.driver', 'database');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-cookieless-session_table.php.stub';
        $migration->up();
        */
    }

    protected function defineRoutes($router): void
    {
        $router->get('/', function () {
            return 'Hello World';
        })
            ->middleware(StartSession::class)
            ->name('index');

        $router->post('/', function () {
            return redirect()->back();
        })
            ->middleware(StartSession::class)
            ->name('store');

        $router->post('csrf', function () {
            return redirect()->back();
        })
            ->middleware([StartSession::class, VerifyCsrfToken::class])
            ->name('store.csrf');

        $router->get('profile', function (Request $request) {
            if ($request->user() !== null) {
                return 'You are logged in!';
            }

            return 'You are not logged in!';
        })
            ->middleware(StartSession::class)
            ->name('profile');

        $router->post('login', function (Request $request) {
            $success = Auth::attempt($request->only('email', 'password'), $request->boolean('remember'));

            if ($success) {
                return redirect()->route('profile');
            }

            return redirect()->route('login');
        })
            ->middleware(StartSession::class)
            ->name('login');
    }
}
