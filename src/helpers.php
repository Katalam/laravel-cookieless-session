<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\HtmlString;

if (! function_exists('session_field')) {
    /**
     * Generate a Session token form field.
     */
    function session_field(): HtmlString
    {
        return new HtmlString('<input type="hidden" name="'.Config::get('cookieless-session.parameter.name').'" value="'.session_token().'" autocomplete="off">');
    }
}

if (! function_exists('session_token')) {
    /**
     * Get the current session token.
     */
    function session_token(): string
    {
        $session = app('session');

        /* @phpstan-ignore-next-line */
        if (isset($session)) {
            return $session->getId();
        }

        /* @phpstan-ignore-next-line */
        throw new RuntimeException('Application session store not set.');
    }
}
