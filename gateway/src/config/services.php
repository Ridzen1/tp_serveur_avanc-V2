<?php

use GuzzleHttp\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    Client::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        return new Client([
            'base_uri' => $settings['api']['base_url'],
            'timeout' => $settings['api']['timeout'],
            'http_errors' => true
        ]);
    },

    LoggerInterface::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $logger = new Logger($settings['logging']['name']);
        
        $logDir = dirname($settings['logging']['path']);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logger->pushHandler(
            new StreamHandler(
                $settings['logging']['path'],
                $settings['logging']['level']
            )
        );
        return $logger;
    }
];
