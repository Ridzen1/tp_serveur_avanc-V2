<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\actions\HomeAction;
use toubilib\api\actions\ListerPraticiensAction;
use toubilib\api\actions\AfficherDetailPraticienAction;
use toubilib\api\actions\ListerCreneauxOccupes;
use toubilib\api\actions\AfficherRdvAction;
use toubilib\api\actions\AfficherAgendaPraticienAction;
use toubilib\api\actions\CreerRendezVousAction;
use toubilib\api\middlewares\ValidationRendezVousMiddleware;
use toubilib\api\middlewares\AuthzRendezVousMiddleware;
use toubilib\api\actions\AnnulerRDVAction;
use toubilib\api\actions\HonorerRdvAction;
use toubilib\api\actions\MarquerNonHonoreRdvAction;
use toubilib\api\actions\LoginAction;
use toubilib\api\actions\SigninAction;
use toubilib\api\actions\CreerPatientAction;
use toubilib\api\middlewares\ValidationPatientMiddleware;
use toubilib\api\actions\ListerConsultationsPatientAction;
use toubilib\api\actions\CreerIndisponibiliteAction;

return function( \Slim\App $app): \Slim\App {

    $app->get('/', HomeAction::class);
    $app->get('/praticiens', ListerPraticiensAction::class);
    $app->get('/praticiens/{id}', AfficherDetailPraticienAction::class);
    $app->get('/praticiens/{id}/creneaux', ListerCreneauxOccupes::class);
    $app->get('/rdvs/{id}', AfficherRdvAction::class)->setName('rdv-detail');
    $app->get('/praticiens/{id}/agenda', AfficherAgendaPraticienAction::class)->setName('agenda-praticien');
    $app->get('/praticiens/{id}/rdvs', AfficherAgendaPraticienAction::class);
    $app->post('/praticiens/{id}/indisponibilites', CreerIndisponibiliteAction::class);
    $app->post('/rdvs/{id}/annuler', AnnulerRDVAction::class);
    $app->post('/rdvs/{id}/honorer', HonorerRdvAction::class);
    $app->post('/rdvs/{id}/non-honore', MarquerNonHonoreRdvAction::class);

    // --- LA LIGNE CRUCIALE EST ICI ---
    // On utilise ::class pour laisser le conteneur faire l'injection
    $app->post('/rdvs', CreerRendezVousAction::class)
        ->add(ValidationRendezVousMiddleware::class);

    $app->post('/inscription', CreerPatientAction::class)
        ->add(ValidationPatientMiddleware::class);
    $app->get('/patients/{id}/consultations', ListerConsultationsPatientAction::class);
    $app->post('/auth/login', LoginAction::class);
    $app->post('/auth/signin', SigninAction::class);

    return $app;
};