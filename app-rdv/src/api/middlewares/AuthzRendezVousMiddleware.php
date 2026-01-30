<?php

namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use toubilib\core\application\usecases\ServiceAuthz;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpForbiddenException;

class AuthzRendezVousMiddleware implements MiddlewareInterface
{
    private ServiceAuthz $serviceAuthz;
    private string $jwtSecret;

    public function __construct(ServiceAuthz $serviceAuthz, string $jwtSecret)
    {
        $this->serviceAuthz = $serviceAuthz;
        $this->jwtSecret = $jwtSecret;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // Extraire le token JWT du header Authorization
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
            // Décoder le JWT (pas besoin de valider car déjà fait par la gateway)
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $userData = (array) $decoded;
            
            // Ajouter les informations de l'utilisateur à la requête
            $request = $request->withAttribute('auth_user', $userData);

        } catch (\Exception $e) {
            throw new HttpUnauthorizedException($request, 'Invalid token: ' . $e->getMessage());
        }

        // Vérifier les autorisations selon la route
        $route = $request->getAttribute('__route__');
        if (!$route) {
            // Si pas de route, continuer (pour compatibilité)
            return $handler->handle($request);
        }

        $routeName = $route->getName();
        $args = $route->getArguments();

        switch ($routeName) {
            case 'agenda-praticien':
                $praticienId = $args['id'] ?? null;
                if (!$praticienId || !$this->serviceAuthz->canAccessAgendaPraticien($praticienId, $userData)) {
                    throw new HttpForbiddenException($request, 'Accès refusé à l\'agenda du praticien');
                }
                break;

            case 'rdv-detail':
                $rdvId = $args['id'] ?? null;
                if (!$rdvId || !$this->serviceAuthz->canAccessRdvDetail($rdvId, $userData)) {
                    throw new HttpForbiddenException($request, 'Accès refusé au détail du rendez-vous');
                }
                break;

            case 'creer-rdv':
                // Pour la création, on récupère les infos du body
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                $praticienId = $data['praticien_id'] ?? null;
                $patientId = $data['patient_id'] ?? null;
                
                if (!$praticienId || !$patientId || 
                    !$this->serviceAuthz->canCreateRdv($praticienId, $patientId, $userData)) {
                    throw new HttpForbiddenException($request, 'Accès refusé à la création de rendez-vous');
                }
                // Remettre le body en place pour les actions suivantes
                $request->getBody()->rewind();
                break;

            default:
                // Pour les autres routes, pas de vérification spécifique
                break;
        }

        return $handler->handle($request);
    }
}
