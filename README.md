# This is my package laravel-cookieless-session

[![Latest Version on Packagist](https://img.shields.io/packagist/v/katalam/laravel-cookieless-session.svg?style=flat-square)](https://packagist.org/packages/katalam/laravel-cookieless-session)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/katalam/laravel-cookieless-session/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/katalam/laravel-cookieless-session/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/katalam/laravel-cookieless-session/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/katalam/laravel-cookieless-session/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/katalam/laravel-cookieless-session.svg?style=flat-square)](https://packagist.org/packages/katalam/laravel-cookieless-session)

## Installation

You can install the package via composer:

```bash
composer require katalam/laravel-cookieless-session
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-cookieless-session-config"
```

This is the contents of the published config file:

```php
return [
    'header' => [
        'name' => 'X-Session-Token', // The name of the header to be used
        'include_with_response' => true, // Whether to include the session token in the response header
    ],
    'parameter' => [
        'name' => '_session_token', // The name of the parameter to be used, either in the query string or in the request body
    ],
];
```

## Usage
Inside `bootstrap/app.php` replace the `StartSession` middleware with the one provided by this package.
```php
use Katalam\Cookieless\Http\Middleware\StartSession;
use Illuminate\Session\Middleware\StartSession as DefaultStartSession;
 
$middleware->web(replace: [
    DefaultStartSession::class => StartSession::class,
]);
```
or use the middleware directly in your routes
```php
use Katalam\Cookieless\Http\Middleware\StartSession;

Route::get('/profile', function () {
    // ...
})->middleware(StartSession::class);
```

## Documentation

The package aims to provide a way to have a website without the ability to dispatch cookies and have a session at the same time.
This is useful for websites that need to be GDPR-compliant and do not want to store any cookies on the user's device.

The technical implementation is based on the following principles:
We have a (new) Middleware named `StartSession` that is responsible for starting the session. We overwrite the default `StartSession` Middleware provided by Laravel at two points:
* We check the presence of cookies in the request and start the session normally if they are present.
* We also check the presence of a header or a parameter in the request. If they are present, we start the session with the (encrypted) session id provided in the header or parameter.
* We also do not send the session cookie in the response if the session was started with a header or parameter.

Now we need to understand two things:
* How is the session data stored?
* How is a user authenticated?

The session data is with various drivers stored in a persistent storage connected to the webserver.
The session is identified by a unique string.
We encrypt this string and send it to the client in some way.
The client sends this string back to the server in the request.
We decrypt this string and use it to identify the session.
This is secure because the string is encrypted the same way as the session id in the cookie.

The user is authenticated by the session.
We pass the request inside the `SessionGuard.php` where
we check if the session has a user id inside the payload attribute.
To determine the key inside the payload for the user id, we have a combination of the word login,
the name of the auth guard and a hash of the absolute namespace from the auth guard.
This is to ensure that the key is unique for each auth guard.

What we essentially do is to replace the session cookie with a header or parameter.
The rest of the internal handling of the session is the same as with the session cookie.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Bruno Görß](https://github.com/Katalam)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
