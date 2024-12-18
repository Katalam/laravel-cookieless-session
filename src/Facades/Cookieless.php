<?php

declare(strict_types=1);

namespace Katalam\Cookieless\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Katalam\Cookieless\Cookieless
 */
class Cookieless extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Katalam\Cookieless\Cookieless::class;
    }
}
