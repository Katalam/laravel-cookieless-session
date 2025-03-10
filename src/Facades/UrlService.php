<?php

namespace Katalam\Cookieless\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Katalam\Cookieless\Services\UrlService
 *
 * @method static string addSessionToUrl(string $url): string
 */
class UrlService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\Cookieless\Services\UrlService::class;
    }
}
