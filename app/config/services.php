<?php
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\infra\repositories\PDOPatientRepository;

use toubilib\core\application\ports\api\ServicePatientInterface;
use toubilib\core\application\usecases\ServicePatient;

return [
    'pdo.patient' => function ($container) {
        $settings = $container->get('settings');
        $dsn = "pgsql:host={$settings['db.patient.host']};port={$settings['db.patient.port']};dbname={$settings['db.patient.name']}";
        return new PDO($dsn, $settings['db.patient.user'], $settings['db.patient.pass']);
    },

    PatientRepositoryInterface::class => function ($container) {
        return new PDOPatientRepository($container->get('pdo.patient'));
    },

    ServicePatientInterface::class => function ($container) {
        return new ServicePatient($container->get('pdo.patient'));
    }
];
