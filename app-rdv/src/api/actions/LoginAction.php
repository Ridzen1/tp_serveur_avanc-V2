<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\usecases\ServiceAuth;

class LoginAction
{
    private ServiceAuth $serviceAuth;

    public function __construct(ServiceAuth $serviceAuth)
    {
        $this->serviceAuth = $serviceAuth;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $data = json_decode((string)$request->getBody(), true) ?: [];
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $dto = $this->serviceAuth->authentication($email, $password);

        if ($dto === null) {
            $payload = json_encode(['error' => 'Invalid credentials']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $response->getBody()->write(json_encode($dto));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}