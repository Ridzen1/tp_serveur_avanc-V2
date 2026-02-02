<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\actions\SigninAction;
use toubilib\api\actions\RegisterAction;
use toubilib\api\actions\RefreshTokenAction;
use toubilib\api\actions\ValidateTokenAction;

return function( \Slim\App $app):\Slim\App {

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode([
            'service' => 'Auth Microservice',
            'version' => '1.0',
            'endpoints' => [
                'POST /auth/signin' => 'Authentification',
                'POST /auth/register' => 'Inscription',
                'POST /auth/refresh' => 'Rafraîchir les tokens',
                'POST /tokens/validate' => 'Valider un token JWT'
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/auth/signin', SigninAction::class);
    $app->post('/auth/register', RegisterAction::class);
    $app->post('/auth/refresh', RefreshTokenAction::class);
    $app->post('/tokens/validate', ValidateTokenAction::class);

    return $app;
};
