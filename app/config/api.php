<?php

use toubilib\api\actions\CreerPatientAction;
use toubilib\core\application\ports\api\ServicePatientInterface;
use toubilib\api\actions\ListerConsultationsPatientAction;

return [
    CreerPatientAction::class => function($container) {
        return new CreerPatientAction($container->get(ServicePatientInterface::class));
    },
    
    ListerConsultationsPatientAction::class => function($container) {
        return new ListerConsultationsPatientAction($container->get(ServicePatientInterface::class));
    }
];