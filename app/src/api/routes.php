<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\actions\HomeAction;
use toubilib\api\actions\CreerPatientAction;
use toubilib\api\middlewares\ValidationPatientMiddleware;
use toubilib\api\actions\ListerConsultationsPatientAction;

return function( \Slim\App $app):\Slim\App {

    $app->get('/', HomeAction::class);
    
    // Routes patients uniquement
    $app->post('/inscription', CreerPatientAction::class)
        ->add(ValidationPatientMiddleware::class);
    $app->get('/patients/{id}/consultations', ListerConsultationsPatientAction::class);

    return $app;
};
