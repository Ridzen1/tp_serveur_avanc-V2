<?php

return [
    'displayErrorDetails' => true,
    'services' => [
        'auth' => [
            'base_url' => 'http://api.auth:80',
            'timeout' => 30
        ],
        'praticiens' => [
            'base_url' => 'http://api.praticiens:80',
            'timeout' => 30
        ],
        'rdv' => [
            'base_url' => 'http://api.rdv:80',
            'timeout' => 30
        ],
        'toubilib' => [
            'base_url' => 'http://api.toubilib:80',
            'timeout' => 30
        ]
    ]
];
