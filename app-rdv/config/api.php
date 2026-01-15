<?php

use Psr\Container\ContainerInterface;

// --- Actions ---
use toubilib\api\actions\ListerPraticiensAction;
use toubilib\api\actions\ListerCreneauxOccupes;
use toubilib\api\actions\AfficherRdvAction;
use toubilib\api\actions\CreerRendezVousAction;
use toubilib\api\actions\CreerPatientAction;
use toubilib\api\actions\SigninAction;
use toubilib\api\actions\ListerConsultationsPatientAction;

// --- Middlewares (C'EST ICI QU'IL MANQUAIT L'IMPORT) ---
use toubilib\api\middlewares\ValidationRendezVousMiddleware;

// --- Interfaces & Services ---
use toubilib\core\application\ports\api\ServicePraticienInterface;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use toubilib\core\application\ports\api\ServicePatientInterface;

// --- NOUVELLE Interface (HTTP) ---
use toubilib\core\application\ports\spi\PraticienServiceInterface;

// --- Repositories ---
use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
// Note: On n'importe plus PraticienRepositoryInterface car on ne l'utilise plus ici !

use toubilib\api\provider\AuthProvider;

return [
    
    CreerPatientAction::class => function(ContainerInterface $container) {
        return new CreerPatientAction($container->get(ServicePatientInterface::class));
    },

    ListerPraticiensAction::class => function(ContainerInterface $container) {
        return new ListerPraticiensAction($container->get(ServicePraticienInterface::class));
    },

    ListerCreneauxOccupes::class => function(ContainerInterface $container) {
        return new ListerCreneauxOccupes($container->get(ServiceRdvInterface::class));
    },

    AfficherRdvAction::class => function(ContainerInterface $container) {
        return new AfficherRdvAction($container->get(ServiceRdvInterface::class));
    },

    CreerRendezVousAction::class => function(ContainerInterface $container) {
        return new CreerRendezVousAction($container->get(ServiceRdvInterface::class));
    },

    ListerConsultationsPatientAction::class => function(ContainerInterface $container) {
        return new ListerConsultationsPatientAction($container->get(ServiceRdvInterface::class));
    },

    // --- LA CORRECTION EST ICI ---
    // 1. On utilise ValidationRendezVousMiddleware::class (qui est maintenant importé en haut)
    // 2. On injecte PraticienServiceInterface (l'adaptateur HTTP)
    ValidationRendezVousMiddleware::class => function (ContainerInterface $c) {
        return new ValidationRendezVousMiddleware(
            $c->get(PraticienServiceInterface::class), // <-- Parfait !
            $c->get(PatientRepositoryInterface::class),
            $c->get(RdvRepositoryInterface::class)
        );
    },

    SigninAction::class => function(ContainerInterface $container) {
        return new SigninAction($container->get(AuthProvider::class));
    }
];