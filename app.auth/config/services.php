<?php

use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use toubilib\infrastructure\repositories\PDOAuthRepository;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\api\provider\AuthProvider;

return [
    'pdo.auth' => function ($container) {
        $settings = $container->get('settings');
        $dsn = "pgsql:host={$settings['db.auth.host']};port={$settings['db.auth.port']};dbname={$settings['db.auth.name']}";
        return new PDO($dsn, $settings['db.auth.user'], $settings['db.auth.pass']);
    },

    AuthRepositoryInterface::class => function ($container) {
        return new PDOAuthRepository($container->get('pdo.auth'));
    },

    ServiceAuth::class => function ($container) {
        return new ServiceAuth($container->get(AuthRepositoryInterface::class));
    },

    AuthProvider::class => function ($container) {
        $settings = $container->get('settings');
        return new AuthProvider(
            $container->get(ServiceAuth::class),
            $container->get(AuthRepositoryInterface::class),
            $settings['jwt']['secret']
        );
    },
];
