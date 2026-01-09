<?php
declare(strict_types=1);

use Gateway\Actions\ListerPraticiensAction;

return function(\Slim\App $app): \Slim\App {
    
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'service' => 'API Gateway Toubilib',
            'version' => '1.0',
            'endpoints' => [
                'GET /praticiens' => 'Liste des praticiens'
            ]
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });

    $app->get('/praticiens', ListerPraticiensAction::class);

    return $app;
};
