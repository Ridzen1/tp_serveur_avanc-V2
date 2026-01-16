<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

// --- Imports des Interfaces RDV ---
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;

// --- Imports des classes concrètes RDV ---
use toubilib\infra\repositories\PDORdvRepository;
use toubilib\infra\repositories\PDOPatientRepository;
use toubilib\core\application\usecases\ServiceRdv;

// --- Adaptateur HTTP pour Praticiens ---
use toubilib\core\application\ports\spi\PraticienServiceInterface;
use toubilib\infrastructure\adaptateurs\HttpPraticienService;

return [

    // -------------------------------------------------------------------------
    // CLIENT HTTP Guzzle pour api.praticiens
    // -------------------------------------------------------------------------
    'client.praticiens' => function (ContainerInterface $c) {
        return new Client([
            'base_uri' => 'http://api.praticiens:80', 
            'timeout' => 5.0,
        ]);
    },

    // -------------------------------------------------------------------------
    // ADAPTATEUR HTTP Praticiens
    // -------------------------------------------------------------------------
    PraticienServiceInterface::class => function (ContainerInterface $c) {
        return new HttpPraticienService($c->get('client.praticiens'));
    },

    // -------------------------------------------------------------------------
    // SERVICE RDV
    // -------------------------------------------------------------------------
    ServiceRdvInterface::class => function (ContainerInterface $container) {
        return new ServiceRdv(
            $container->get(RdvRepositoryInterface::class),
            $container->get(PraticienServiceInterface::class),
            $container->get(PatientRepositoryInterface::class)
        );
    },

    // -------------------------------------------------------------------------
    // BASES DE DONNÉES
    // -------------------------------------------------------------------------
    
    'pdo.rdv' => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $dsn = "pgsql:host={$settings['db.rdv.host']};dbname={$settings['db.rdv.name']}";
        return new PDO($dsn, $settings['db.rdv.user'], $settings['db.rdv.pass']);
    },

    'pdo.patient' => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $dsn = "pgsql:host={$settings['db.patient.host']};port={$settings['db.patient.port']};dbname={$settings['db.patient.name']}";
        return new PDO($dsn, $settings['db.patient.user'], $settings['db.patient.pass']);
    },

    // -------------------------------------------------------------------------
    // REPOSITORIES
    // -------------------------------------------------------------------------

    RdvRepositoryInterface::class => function (ContainerInterface $container) {
        return new PDORdvRepository($container->get('pdo.rdv'));
    },

    PatientRepositoryInterface::class => function (ContainerInterface $container) {
        return new PDOPatientRepository($container->get('pdo.patient'));
    }
];