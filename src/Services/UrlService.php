<?php

declare(strict_types=1);

namespace Katalam\Cookieless\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

class UrlService
{
    public function addSessionToUrl(string $url): string
    {
        $parsedUrl = parse_url($url);
        $query = $parsedUrl['query'] ?? '';

        $query = str($query)
            ->explode('&')
            ->mapWithKeys(function (string $item) {
                if (empty($item)) {
                    return [];
                }

                [$key, $value] = explode('=', $item, 2);

                return [$key => $value];
            })
            ->toArray();

        $query[Config::get('cookieless-session.parameter.name')] = Crypt::encrypt(session()?->getId());

        $parsedUrl['query'] = http_build_query($query);

        return $parsedUrl['scheme'].'://'.$parsedUrl['host'].($parsedUrl['path'] ?? '').'?'.$parsedUrl['query'];
    }
}
