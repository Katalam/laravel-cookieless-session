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
    ],
];
