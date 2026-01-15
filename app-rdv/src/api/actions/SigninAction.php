<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\api\provider\AuthProvider;
use Respect\Validation\Validator as v;

class SigninAction
{
    private AuthProvider $authProvider;

    public function __construct(AuthProvider $authProvider)
    {
        $this->authProvider = $authProvider;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = json_decode((string)$request->getBody(), true) ?: [];
        
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (empty($email) || empty($password)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Email and password are required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (!v::email()->validate($email)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Invalid email format'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (!is_string($email) || !is_string($password)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Email and password must be strings'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $authTokenDTO = $this->authProvider->signin($email, $password);

            if ($authTokenDTO === null) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Authentication successful',
                'data' => $authTokenDTO->toArray()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Authentication failed',
                'details' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}