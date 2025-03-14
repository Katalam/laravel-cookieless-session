<?php

declare(strict_types=1);

// config for Katalam/Cookieless
return [
    'header' => [
        'name' => 'X-Session-Token',
        'include_with_response' => true,
    ],
    'parameter' => [
        'name' => '_session_token',
        'include_with_response' => true,
    ],
    'cookie' => [
        'force_no_cookie' => false,
    ],
];
