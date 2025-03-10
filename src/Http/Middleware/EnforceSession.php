<?php

declare(strict_types=1);

namespace Katalam\Cookieless\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Katalam\Cookieless\Facades\UrlService;
use Symfony\Component\HttpFoundation\Response;

class EnforceSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->has(Config::get('cookieless-session.parameter.name'))) {
            $newUrl = UrlService::addSessionToUrl($request->getUri());

            return redirect()->to($newUrl);
        }

        return $next($request);
    }
}
