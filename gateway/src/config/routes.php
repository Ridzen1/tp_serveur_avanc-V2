<?php
declare(strict_types=1);

use Gateway\Actions\GenericProxyAction;

return function(\Slim\App $app): \Slim\App {
    
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'service' => 'API Gateway Toubilib',
            'version' => '1.0',
            'endpoints' => [
                'GET /praticiens' => 'Liste des praticiens',
                'GET /praticiens/{id}' => 'Détails d\'un praticien',
                'ANY /rdvs[/{params}]' => 'Gestion des rendez-vous (GET, POST, PUT, DELETE)',
                'GET /praticiens/{id}/rdvs' => 'Liste des rendez-vous d\'un praticien (ex: ?date_debut=2020-01-01&date_fin=2030-12-31)'
                
            ]
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });

    $app->get('/praticiens', GenericProxyAction::class);
    $app->get('/praticiens/{id}', GenericProxyAction::class);
    $app->get('/praticiens/{id}/rdvs', GenericProxyAction::class);
    $app->map(['GET', 'POST', 'PUT', 'DELETE'], '/rdvs[/{params:.*}]', GenericProxyAction::class);

    return $app;
};
