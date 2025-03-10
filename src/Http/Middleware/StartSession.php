<?php

declare(strict_types=1);

namespace Katalam\Cookieless\Http\Middleware;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Katalam\Cookieless\Facades\UrlService;
use Symfony\Component\HttpFoundation\Response;

class StartSession extends \Illuminate\Session\Middleware\StartSession
{
    /**
     * Get the session implementation from the manager.
     */
    public function getSession(Request $request): Session
    {
        return tap($this->manager->driver(), static function (Store $session) use ($request) {
            if ($request->cookies->has($session->getName())) {
                $session->setId($request->cookies->get($session->getName()));
            } elseif ($request->headers->has(Config::get('cookieless-session.header.name')) || $request->has(Config::get('cookieless-session.parameter.name'))) {
                $sessionToken = $request->headers->get(Config::get('cookieless-session.header.name'), $request->input(Config::get('cookieless-session.parameter.name')));
                try {
                    $sessionToken = Crypt::decrypt($sessionToken);
                } catch (DecryptException) {
                    $session->setId(null);

                    return;
                }
                $session->setId($sessionToken);
            }
        });
    }

    /**
     * Handle the given request within session state.
     *
     * @return mixed
     */
    protected function handleStatefulRequest(Request $request, $session, Closure $next)
    {
        // If a session driver has been configured, we will need to start the session here
        // so that the data is ready for an application. Note that the Laravel sessions
        // do not make use of PHP "native" sessions in any way since they are crappy.
        $request->setLaravelSession(
            $this->startSession($request, $session)
        );

        $this->collectGarbage($session);

        $response = $next($request);

        $this->storeCurrentUrl($request, $session);

        $hasSessionInHeader = $request->headers->has(Config::get('cookieless-session.header.name'));
        $hasSessionInParameter = $request->has(Config::get('cookieless-session.parameter.name'));

        if ((! ($hasSessionInHeader || $hasSessionInParameter)) && ! Config::get('cookieless-session.cookie.force_no_cookie')) {
            $this->addCookieToResponse($response, $session);
        } elseif (Config::get('cookieless-session.parameter.include_with_response')) {
            $this->addGetParameterToResponse($response, $session);
        }

        if (Config::get('cookieless-session.header.include_with_response')) {
            $this->addHeaderToResponse($response, $session);
        }

        // Again, if the session has been configured we will need to close out the session
        // so that the attributes may be persisted to some storage medium. We will also
        // add the session identifier cookie to the application response headers now.
        $this->saveSession($request);

        return $response;
    }

    protected function addHeaderToResponse(Response $response, Session $session): void
    {
        $response->headers->set(Config::get('cookieless-session.header.name'), Crypt::encrypt($session->getId()));
    }

    protected function addGetParameterToResponse(Response $response, Session $session): void
    {
        if ($response->isRedirection()) {
            /** @var RedirectResponse $response */
            $newUrl = UrlService::addSessionToUrl($response->getTargetUrl());

            $response->setTargetUrl($newUrl);
        }
    }
}
