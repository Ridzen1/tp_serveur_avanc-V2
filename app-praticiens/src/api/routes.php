<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\actions\HomeAction;
use toubilib\api\actions\ListerPraticiensAction;
use toubilib\api\actions\AfficherDetailPraticienAction;
use toubilib\api\actions\ListerCreneauxOccupes;
use toubilib\api\actions\AfficherAgendaPraticienAction;
use toubilib\api\actions\CreerIndisponibiliteAction;

return function( \Slim\App $app):\Slim\App {

    $app->get('/', HomeAction::class);
    $app->get('/praticiens', ListerPraticiensAction::class);
    $app->get('/praticiens/{id}', AfficherDetailPraticienAction::class);
    $app->get('/praticiens/{id}/creneaux', ListerCreneauxOccupes::class);
    $app->get('/praticiens/{id}/agenda', AfficherAgendaPraticienAction::class)->setName('agenda-praticien');
    $app->post('/praticiens/{id}/indisponibilites', CreerIndisponibiliteAction::class);

    return $app;
};
