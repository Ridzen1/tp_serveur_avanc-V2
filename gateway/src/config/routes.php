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
                'GET /praticiens/{id}' => 'Détails d\'un praticien'
            ]
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });

    $app->get('/praticiens', GenericProxyAction::class);
    $app->get('/praticiens/{id}', GenericProxyAction::class);
    $app->map(['GET', 'POST', 'PUT', 'DELETE'], '/rdvs[/{params:.*}]', \Gateway\Actions\GenericProxyAction::class);

    return $app;
};
