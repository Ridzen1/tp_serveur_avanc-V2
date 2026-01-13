<?php
use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\infra\repositories\PDOPraticienRepository;
use toubilib\core\application\usecases\ServicePraticien;
use toubilib\core\application\ports\api\ServicePraticienInterface;

return [
    'pdo.praticien' => function ($container) {
        $settings = $container->get('settings');
        $dsn = "pgsql:host={$settings['db.praticien.host']};port={$settings['db.praticien.port']};dbname={$settings['db.praticien.name']}";
        return new PDO($dsn, $settings['db.praticien.user'], $settings['db.praticien.pass']);
    },

    PraticienRepositoryInterface::class => function ($container) {
        return new PDOPraticienRepository($container->get('pdo.praticien'));
    },

    ServicePraticienInterface::class => function ($container) {
        return new ServicePraticien($container->get(PraticienRepositoryInterface::class));
    }
];
