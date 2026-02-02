<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\provider\AuthProvider;

class ValidateTokenAction
{
    private AuthProvider $authProvider;

    public function __construct(AuthProvider $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Extraire le token depuis le header Authorization ou le body
        $authHeader = $request->getHeaderLine('Authorization');
        $token = null;

        if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            // Si pas dans le header, chercher dans le body
            $data = json_decode((string)$request->getBody(), true) ?: [];
            $token = $data['token'] ?? null;
        }

        // Validation du token
        if (empty($token)) {
            $response->getBody()->write(json_encode([
                'valid' => false,
                'message' => 'Token is required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $tokenInfo = $this->authProvider->validateToken($token);

            $response->getBody()->write(json_encode([
                'valid' => true,
                'message' => 'Token is valid',
                'data' => $tokenInfo
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'valid' => false,
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
