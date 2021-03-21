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
    'log_paths' => [
        'auth' =>  'logs/auth',
        'core' => 'logs/core',
    ],
//    'user_model' => \App\Models\User::class,
    'user_model' => \RushApp\Core\Models\User::class,
];