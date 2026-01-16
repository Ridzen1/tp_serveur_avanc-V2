<?php

use Psr\Container\ContainerInterface;

// --- Actions RDV ---
use toubilib\api\actions\AfficherRdvAction;
use toubilib\api\actions\CreerRendezVousAction;
use toubilib\api\actions\AnnulerRDVAction;
use toubilib\api\actions\HonorerRdvAction;
use toubilib\api\actions\MarquerNonHonoreRdvAction;
use toubilib\api\actions\ListerConsultationsPatientAction;

// --- Middlewares ---
use toubilib\api\middlewares\ValidationRendezVousMiddleware;

// --- Interfaces & Services ---
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\spi\PraticienServiceInterface;

// --- Repositories ---
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;

return [
    
    AfficherRdvAction::class => function(ContainerInterface $container) {
        return new AfficherRdvAction($container->get(ServiceRdvInterface::class));
    },

    CreerRendezVousAction::class => function(ContainerInterface $container) {
        return new CreerRendezVousAction($container->get(ServiceRdvInterface::class));
    },

    AnnulerRDVAction::class => function(ContainerInterface $container) {
        return new AnnulerRDVAction($container->get(ServiceRdvInterface::class));
    },

    HonorerRdvAction::class => function(ContainerInterface $container) {
        return new HonorerRdvAction($container->get(ServiceRdvInterface::class));
    },

    MarquerNonHonoreRdvAction::class => function(ContainerInterface $container) {
        return new MarquerNonHonoreRdvAction($container->get(ServiceRdvInterface::class));
    },

    ListerConsultationsPatientAction::class => function(ContainerInterface $container) {
        return new ListerConsultationsPatientAction($container->get(ServiceRdvInterface::class));
    },
    
    ValidationRendezVousMiddleware::class => function (ContainerInterface $c) {
        return new ValidationRendezVousMiddleware(
            $c->get(PraticienServiceInterface::class),
            $c->get(PatientRepositoryInterface::class),
            $c->get(RdvRepositoryInterface::class)
        );
    }
];