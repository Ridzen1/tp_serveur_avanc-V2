<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\actions\HomeAction;
use toubilib\api\actions\AfficherRdvAction;
use toubilib\api\actions\CreerRendezVousAction;
use toubilib\api\middlewares\ValidationRendezVousMiddleware;
use toubilib\api\actions\AnnulerRDVAction;
use toubilib\api\actions\HonorerRdvAction;
use toubilib\api\actions\MarquerNonHonoreRdvAction;
use toubilib\api\actions\ListerConsultationsPatientAction;
use toubilib\api\actions\AfficherAgendaPraticienAction;

return function( \Slim\App $app): \Slim\App {

    $app->get('/', HomeAction::class);
    $app->get('/rdvs/{id}', AfficherRdvAction::class)->setName('rdv-detail');
    $app->get('/praticiens/{id}/agenda', AfficherAgendaPraticienAction::class)->setName('agenda-praticien');
    $app->get('/praticiens/{id}/rdvs', AfficherAgendaPraticienAction::class);
    $app->post('/praticiens/{id}/indisponibilites', CreerIndisponibiliteAction::class);
    $app->post('/rdvs/{id}/annuler', AnnulerRDVAction::class);
    $app->post('/rdvs/{id}/honorer', HonorerRdvAction::class);
    $app->post('/rdvs/{id}/non-honore', MarquerNonHonoreRdvAction::class);
    $app->post('/rdvs', CreerRendezVousAction::class)
        ->add(ValidationRendezVousMiddleware::class);
    $app->get('/patients/{id}/consultations', ListerConsultationsPatientAction::class);

    return $app;
};