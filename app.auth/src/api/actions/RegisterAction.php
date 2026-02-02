<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\usecases\ServiceAuth;

class RegisterAction
{
    private ServiceAuth $serviceAuth;

    public function __construct(ServiceAuth $serviceAuth)
    {
        $this->serviceAuth = $serviceAuth;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = json_decode((string)$request->getBody(), true) ?: [];
        
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Validation des données
        if (empty($email) || empty($password)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Email and password are required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validation du format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Invalid email format'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $authDTO = $this->serviceAuth->register($email, $password, '1');

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $authDTO->id,
                    'email' => $authDTO->email,
                    'role' => $authDTO->role
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\Exception $e) {
            // Gérer le cas où l'utilisateur existe déjà
            $statusCode = (strpos($e->getMessage(), 'already exists') !== false || 
                          strpos($e->getMessage(), 'duplicate') !== false) ? 409 : 500;
            
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
        }
    }
}
