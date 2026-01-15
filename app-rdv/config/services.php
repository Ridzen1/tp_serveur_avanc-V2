<?php

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

// --- Imports des Interfaces ---
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\AuthRepositoryInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\api\ServiceAuthzInterface;
use toubilib\core\application\ports\api\ServicePatientInterface;

// --- Imports des classes concrètes (Implémentations) ---
use toubilib\infra\repositories\PDORdvRepository;
use toubilib\infra\repositories\PDOPatientRepository;
use toubilib\infrastructure\repositories\PDOAuthRepository;
use toubilib\core\application\usecases\ServiceRdv;
use toubilib\core\application\usecases\ServiceAuth;
use toubilib\core\application\usecases\ServiceAuthz;
use toubilib\core\application\usecases\ServicePatient;
use toubilib\api\provider\AuthProvider;

// --- NOUVEAUX IMPORTS POUR EXERCICE 4 (Adaptateur) ---
// Attention aux namespaces que tu as utilisés dans les fichiers précédents
use toubilib\core\application\ports\spi\PraticienServiceInterface;
use toubilib\infrastructure\adaptateurs\HttpPraticienService;

return [

    // -------------------------------------------------------------------------
    // 1. CONFIGURATION DU CLIENT HTTP (Guzzle) pour api.praticiens
    // -------------------------------------------------------------------------
    'client.praticiens' => function (ContainerInterface $c) {
        return new Client([
            // 'api.praticiens' est le nom du service dans docker-compose
            'base_uri' => 'http://api.praticiens', 
            'timeout' => 5.0,
        ]);
    },

    // -------------------------------------------------------------------------
    // 2. ADAPTATEUR : Quand on veut PraticienServiceInterface, on donne l'Adaptateur HTTP
    // -------------------------------------------------------------------------
    PraticienServiceInterface::class => function (ContainerInterface $c) {
        return new HttpPraticienService($c->get('client.praticiens'));
    },

    // -------------------------------------------------------------------------
    // 3. SERVICE RDV : Injection mise à jour
    // -------------------------------------------------------------------------
    ServiceRdvInterface::class => function (ContainerInterface $container) {
        return new ServiceRdv(
            $container->get(RdvRepositoryInterface::class),
            $container->get(PraticienServiceInterface::class), // <-- C'est ici que ça change (Adaptateur HTTP)
            $container->get(PatientRepositoryInterface::class)
        );
    },

    // -------------------------------------------------------------------------
    // BASES DE DONNÉES (On garde RDV, Patient et Auth, on vire Praticien)
    // -------------------------------------------------------------------------
    
    // PDO RDV
    'pdo.rdv' => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $dsn = "pgsql:host={$settings['db.rdv.host']};dbname={$settings['db.rdv.name']}";
        return new PDO($dsn, $settings['db.rdv.user'], $settings['db.rdv.pass']);
    },

    // PDO Patient (On garde car app-rdv a encore besoin de vérifier l'existence du patient ?)
    // Note: Idéalement, Patient devrait aussi être un microservice, mais pour l'exo on se focus sur Praticien.
    'pdo.patient' => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $dsn = "pgsql:host={$settings['db.patient.host']};port={$settings['db.patient.port']};dbname={$settings['db.patient.name']}";
        return new PDO($dsn, $settings['db.patient.user'], $settings['db.patient.pass']);
    },

    // PDO Auth
    'pdo.auth' => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        $dsn = "pgsql:host={$settings['db.auth.host']};port={$settings['db.auth.port']};dbname={$settings['db.auth.name']}";
        return new PDO($dsn, $settings['db.auth.user'], $settings['db.auth.pass']);
    },

    // -------------------------------------------------------------------------
    // REPOSITORIES & SERVICES CLASSIQUES
    // -------------------------------------------------------------------------

    RdvRepositoryInterface::class => function (ContainerInterface $container) {
        return new PDORdvRepository($container->get('pdo.rdv'));
    },

    PatientRepositoryInterface::class => function (ContainerInterface $container) {
        return new PDOPatientRepository($container->get('pdo.patient'));
    },

    ServicePatientInterface::class => function (ContainerInterface $container) {
        return new ServicePatient($container->get('pdo.patient'));
    },

    AuthRepositoryInterface::class => function (ContainerInterface $container) {
        return new PDOAuthRepository($container->get('pdo.auth'));
    },

    ServiceAuth::class => function (ContainerInterface $container) {
        return new ServiceAuth($container->get(AuthRepositoryInterface::class));
    },

    AuthProvider::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');
        return new AuthProvider(
            $container->get(ServiceAuth::class),
            $settings['jwt']['secret']
        );
    },

    ServiceAuthzInterface::class => function (ContainerInterface $container) {
        return new ServiceAuthz($container->get(RdvRepositoryInterface::class));
    },
];