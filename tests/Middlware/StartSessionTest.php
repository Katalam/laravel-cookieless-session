<?php

declare(strict_types=1);

use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\post;

dataset('options', [
    'parameter' => function () {
        $sessionId = getValidSessionId();

        return [
            [Config::get('cookieless-session.header.name') => Crypt::encrypt($sessionId)],
            [],
            $sessionId,
        ];
    },
    'header' => function () {
        $sessionId = getValidSessionId();

        return [
            [],
            [Config::get('cookieless-session.parameter.name') => Crypt::encrypt($sessionId)],
            $sessionId,
        ];
    },
]);

describe('get requests', function () {
    test('without session', function () {
        getIndexAssertOkAndSeeHelloWorld();
    });

    test('with old session', function (array $header, array $parameters) {
        getIndexAssertOkAndSeeHelloWorld($header, $parameters);

        $countedSessions = DB::table('sessions')->count();

        expect($countedSessions)->toBe(1);
    })->with('options');

    test('with persistent session', function (array $header, array $parameters, string $sessionId) {
        insertSession($sessionId);

        getIndexAssertOkAndSeeHelloWorld($header, $parameters);

        $countedSessions = DB::table('sessions')->count();

        expect($countedSessions)->toBe(1);
    })->with('options');
});

describe('post requests', function () {
    test('without session', function () {
        postIndexAssertRedirectToIndex();
    });

    test('with old session', function (array $header, array $parameters) {
        postIndexAssertRedirectToIndex($header, $parameters);

        $countedSessions = DB::table('sessions')->count();

        expect($countedSessions)->toBe(1);
    })->with('options');

    test('with persistent session', function (array $header, array $parameters, string $sessionId) {
        insertSession($sessionId);

        postIndexAssertRedirectToIndex($header, $parameters);

        $countedSessions = DB::table('sessions')->count();

        expect($countedSessions)->toBe(1);
    })->with('options');

    test('with persistent session and csrf', function (array $header, array $parameters, string $sessionId, string $token) {
        insertSession($sessionId, [
            '_token' => $token,
        ]);

        post(route('store.csrf', $parameters), [], $header)
            ->assertRedirectContains(route('index'));

        $countedSessions = DB::table('sessions')->count();

        expect($countedSessions)->toBe(1);
    })->with([
        'parameter' => function () {
            $sessionId = getValidSessionId();
            $token = getValidSessionId();

            return [
                [Config::get('cookieless-session.header.name') => Crypt::encrypt($sessionId)],
                ['_token' => $token],
                $sessionId,
                $token,
            ];
        },
        'header' => function () {
            $sessionId = getValidSessionId();
            $token = getValidSessionId();

            return [
                [],
                [
                    Config::get('cookieless-session.parameter.name') => Crypt::encrypt($sessionId),
                    '_token' => $token,
                ],
                $sessionId,
                $token,
            ];
        },
    ]);
});

describe('authenticate requests', function () {
    test('logged in requests', function (array $header, array $parameters, string $sessionId) {
        $user = generateUser();

        insertSession($sessionId, [
            'login_web_'.sha1(SessionGuard::class) => $user->id,
        ], [
            'user_id' => $user->id, // not needed, but for better readability
        ]);

        getIndexAssertLoggedIn($header, $parameters);

        $countedSessions = DB::table('sessions')->count();

        expect($countedSessions)->toBe(1);
    })->with('options');

    test('logging in requests', function (array $header, array $parameters, string $sessionId) {
        $user = generateUser();

        insertSession($sessionId);

        $parameters = [
            ...$parameters,
            'email' => $user->email,
            'password' => 'password',
        ];

        postLoginAssertLoggedIn($header, $parameters);

        $countedSessions = DB::table('sessions')->count();

        expect($countedSessions)->toBe(1);

        $session = DB::table('sessions')->first();

        expect($session->user_id)->toBe($user->id);
    })->with('options');

    test('logging in without session', function () {
        $user = generateUser();

        $parameters = [
            'email' => $user->email,
            'password' => 'password',
            Config::get('cookieless-session.parameter.name') => 'invalid',
        ];

        postLoginAssertLoggedIn([], $parameters);

        $countedSessions = DB::table('sessions')->count();

        expect($countedSessions)->toBe(1);

        $session = DB::table('sessions')->first();

        expect($session->user_id)->toBe($user->id);
    });

    test('logging in requests with cookie returns cookie', function () {
        $user = generateUser();

        $parameters = [
            'email' => $user->email,
            'password' => 'password',
        ];

        post(route('login', $parameters))
            ->assertRedirect('profile?')
            ->assertCookie('laravel_session');

        $countedSessions = DB::table('sessions')->count();

        expect($countedSessions)->toBe(1);

        $session = DB::table('sessions')->first();

        expect($session->user_id)->toBe($user->id);
    });
});
