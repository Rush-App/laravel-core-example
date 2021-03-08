<?php

return [
    'action_names' => [
        'index',
        'show',
        'store',
        'update',
        'destroy',
    ],
    'default_cache_ttl' => 24*60*60, // 24 hours
    'http_statuses' => [
        'forbidden' => 403,
    ],
];