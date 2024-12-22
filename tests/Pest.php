<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Katalam\Cookieless\Tests\Fixtures\User;
use Katalam\Cookieless\Tests\TestCase;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(TestCase::class)->in(__DIR__);

function getIndexAssertOkAndSeeHelloWorld(array $headers = [], array $parameters = []): void
{
    get(route('index', $parameters), $headers)
        ->assertOk()
        ->assertSee('Hello World')
        ->assertHeader(Config::get('cookieless-session.header.name'));
}

function postIndexAssertRedirectToIndex(array $headers = [], array $parameters = [], string $sessionId = ''): void
{
    post(route('store', $parameters), [], $headers)
        ->when($sessionId !== '', function (TestResponse $response) use ($sessionId) {
            return $response->assertRedirectToRoute('index', [
                Config::get('cookieless-session.parameter.name') => Crypt::encrypt($sessionId),
            ]);
        }, function (TestResponse $response) {
            return $response->assertRedirectContains(route('index'));
        });
}

function getIndexAssertLoggedIn(array $headers = [], array $parameters = []): void
{
    get(route('profile', $parameters), $headers)
        ->assertOk()
        ->assertSee('You are logged in!')
        ->assertDontSee('You are not logged in!');
}

function postLoginAssertLoggedIn(array $headers = [], array $parameters = [], string $sessionId = ''): void
{
    post(route('login', $parameters), [], $headers)
        ->when($sessionId !== '', function (TestResponse $response) use ($sessionId) {
            return $response->assertRedirectToRoute('profile', [
                Config::get('cookieless-session.parameter.name') => Crypt::encrypt($sessionId),
            ]);
        }, function (TestResponse $response) {
            return $response->assertRedirectContains(route('profile'));
        });
}

function getValidSessionId(): string
{
    return Str::random(40);
}

function insertSession(string $sessionId, array $payload = [], array $options = []): void
{
    DB::table('sessions')->insert([
        'id' => $sessionId,
        'user_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Symfony',
        'payload' => base64_encode(@serialize($payload)),
        'last_activity' => now()->timestamp,
        ...$options,
    ]);
}

function generateUser(array $data = []): User
{
    return User::create([
        'name' => 'John Doe',
        'email' => 'foo@bar.de',
        'password' => Hash::make('password'),
        ...$data,
    ]);
}
