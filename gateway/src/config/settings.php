<?php

return [
    'displayErrorDetails' => true,
    'api' => [
        'base_url' => 'http://api.toubilib:80',
        'timeout' => 30
    ],
    'logging' => [
        'name' => 'gateway',
        'path' => __DIR__ . '/../var/logs/gateway.log',
        'level' => \Monolog\Logger::DEBUG
    ]
];
