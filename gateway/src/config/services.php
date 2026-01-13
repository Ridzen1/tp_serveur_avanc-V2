<?php

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

return [
    Client::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['api']['base_url'],
            'timeout' => $settings['api']['timeout'],
            'http_errors' => true
        ]);
    }
];
