<?php
declare(strict_types=1);

namespace Gateway\Middlewares;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;

class AuthMiddleware implements MiddlewareInterface
{
    private Client $authClient;

    public function __construct(Client $authClient)
    {
        $this->authClient = $authClient;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        
        // Extraire le token depuis le header Authorization
        $authHeader = $request->getHeaderLine('Authorization');
        $token = null;

        if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Si pas de token, lever une exception 401
        if (empty($token)) {
            throw new HttpUnauthorizedException($request, 'Authentication token is required');
        }

        try {
            // Valider le token auprès du microservice d'authentification
            $response = $this->authClient->request('POST', '/tokens/validate', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json'
                ]
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            // Vérifier que le token est valide
            if (!isset($body['valid']) || $body['valid'] !== true) {
                throw new HttpUnauthorizedException($request, 'Invalid authentication token');
            }

            // Ajouter les informations de l'utilisateur à la requête pour les actions suivantes
            if (isset($body['data'])) {
                $request = $request->withAttribute('auth_user', $body['data']);
            }

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Erreur 4xx du service d'authentification (token invalide, expiré, etc.)
            $statusCode = $e->getResponse()->getStatusCode();
            
            if ($statusCode === 401) {
                throw new HttpUnauthorizedException($request, 'Invalid or expired authentication token');
            }
            
            throw new HttpUnauthorizedException($request, 'Authentication failed');

        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // Erreur 5xx du service d'authentification
            throw new HttpUnauthorizedException($request, 'Authentication service unavailable');

        } catch (\Exception $e) {
            // Autres erreurs de validation de token
            if ($e instanceof HttpUnauthorizedException) {
                throw $e;
            }
            throw new HttpUnauthorizedException($request, 'Authentication failed: ' . $e->getMessage());
        }
        
        // Si la validation du token a réussi, continuer vers le handler suivant
        // Les exceptions levées par le handler ne seront pas attrapées ici
        return $handler->handle($request);
    }
}
