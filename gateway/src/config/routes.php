<?php
declare(strict_types=1);

use Gateway\Actions\GenericProxyAction;
use Gateway\Middlewares\AuthMiddleware;

return function(\Slim\App $app): \Slim\App {
    
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'service' => 'API Gateway Toubilib',
            'version' => '1.0',
            'endpoints' => [
                'GET /praticiens' => 'Liste des praticiens',
                'GET /praticiens/{id}' => 'Détails d\'un praticien',
                'GET /praticiens/{id}/agenda' => 'Agenda d\'un praticien (Requiert authentification)',
                'GET /rdvs/{id}' => 'Détail d\'un rendez-vous (Requiert authentification)',
                'POST /rdvs' => 'Créer un rendez-vous (Requiert authentification)',
                'GET /praticiens/{id}/rdvs' => 'Liste des rendez-vous d\'un praticien'
            ]
        ]));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });

    // Routes publiques (sans authentification)
    $app->get('/praticiens', GenericProxyAction::class);
    $app->get('/praticiens/{id}', GenericProxyAction::class);
    $app->get('/praticiens/{id}/rdvs', GenericProxyAction::class);
    
    // Routes protégées (avec authentification)
    $app->get('/praticiens/{id}/agenda', GenericProxyAction::class)->add(AuthMiddleware::class);
    $app->get('/rdvs/{id}', GenericProxyAction::class)->add(AuthMiddleware::class);
    $app->post('/rdvs', GenericProxyAction::class)->add(AuthMiddleware::class);
    
    // Autres routes RDV (PUT, DELETE) - également protégées
    $app->put('/rdvs/{id}', GenericProxyAction::class)->add(AuthMiddleware::class);
    $app->delete('/rdvs/{id}', GenericProxyAction::class)->add(AuthMiddleware::class);

    // Routes d'authentification (publiques)
    $app->post('/auth/signin', GenericProxyAction::class);
    $app->post('/auth/register', GenericProxyAction::class);
    $app->post('/auth/refresh', GenericProxyAction::class);
    
    // Route de validation de token (publique pour permettre la validation)
    $app->post('/tokens/validate', GenericProxyAction::class);

    return $app;
};
