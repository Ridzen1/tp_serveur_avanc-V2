<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\provider\AuthProvider;

class RefreshTokenAction
{
    private AuthProvider $authProvider;

    public function __construct(AuthProvider $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = json_decode((string)$request->getBody(), true) ?: [];
        
        $refreshToken = $data['refresh_token'] ?? null;

        // Validation
        if (empty($refreshToken)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Refresh token is required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $tokens = $this->authProvider->refresh($refreshToken);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Invalid or expired refresh token'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
