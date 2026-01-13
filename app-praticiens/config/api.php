<?php

use toubilib\api\actions\ListerPraticiensAction;
use toubilib\api\actions\AfficherDetailPraticienAction;
use toubilib\api\actions\ListerCreneauxOccupes;
use toubilib\api\actions\AfficherAgendaPraticienAction;
use toubilib\api\actions\CreerIndisponibiliteAction;
use toubilib\core\application\ports\api\ServicePraticienInterface;

return [
    ListerPraticiensAction::class => function($container) {
        return new ListerPraticiensAction($container->get(ServicePraticienInterface::class));
    },
    AfficherDetailPraticienAction::class => function($container) {
        return new AfficherDetailPraticienAction($container->get(ServicePraticienInterface::class));
    },
    ListerCreneauxOccupes::class => function($container) {
        return new ListerCreneauxOccupes($container->get(ServicePraticienInterface::class));
    },
    AfficherAgendaPraticienAction::class => function($container) {
        return new AfficherAgendaPraticienAction($container->get(ServicePraticienInterface::class));
    },
    CreerIndisponibiliteAction::class => function($container) {
        return new CreerIndisponibiliteAction($container->get(ServicePraticienInterface::class));
    }
];