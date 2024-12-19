<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

function postIndexAssertRedirectToIndex(array $headers = [], array $parameters = []): void
{
    post(route('store', $parameters), [], $headers)
        ->assertRedirectToRoute('index');
}

function getIndexAssertLoggedIn(array $headers = [], array $parameters = []): void
{
    get(route('profile', $parameters), $headers)
        ->assertOk()
        ->assertSee('You are logged in!')
        ->assertDontSee('You are not logged in!');
}

function postLoginAssertLoggedIn(array $headers = [], array $parameters = []): void
{
    post(route('login', $parameters), [], $headers)
        ->assertRedirectToRoute('profile');
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
