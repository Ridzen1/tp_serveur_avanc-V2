<?php

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

return [
    'client.praticiens' => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['services']['praticiens']['base_url'],
            'timeout' => $settings['services']['praticiens']['timeout'],
            'http_errors' => true
        ]);
    },
    'toubilib.client' => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['services']['toubilib']['base_url'],
            'timeout' => $settings['services']['toubilib']['timeout'],
            'http_errors' => true
        ]);
    },
    'client.rdv' => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['services']['rdv']['base_url'],
            'timeout' => $settings['services']['rdv']['timeout'],
            'http_errors' => true
        ]);
    }
];