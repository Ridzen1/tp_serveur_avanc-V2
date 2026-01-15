<?php

namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\dto\AuthDTO;
use toubilib\core\application\usecases\ServiceAuthz;

class AuthzRendezVousMiddleware
{
    private ServiceAuthz $serviceAuthz;

    public function __construct(ServiceAuthz $serviceAuthz)
    {
        $this->serviceAuthz = $serviceAuthz;
    }

    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $user = $request->getAttribute('user');
        if (!$user instanceof AuthDTO) {
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(['error' => 'Utilisateur non authentifié']));
        }

        $route = $request->getAttribute('route');
        if (!$route) {
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(['error' => 'Route non trouvée']));
        }

        $routeName = $route->getName();
        $args = $route->getArguments();

        switch ($routeName) {
            case 'agenda-praticien':
                $praticienId = $args['id'] ?? null;
                if (!$praticienId || !$this->serviceAuthz->canAccessAgendaPraticien($praticienId, $user)) {
                    return $response->withStatus(403)->withHeader('Content-Type', 'application/json')
                        ->getBody()->write(json_encode(['error' => 'Accès refusé à l\'agenda du praticien']));
                }
                break;

            case 'rdv-detail':
                $rdvId = $args['id'] ?? null;
                if (!$rdvId || !$this->serviceAuthz->canAccessRdvDetail($rdvId, $user)) {
                    return $response->withStatus(403)->withHeader('Content-Type', 'application/json')
                        ->getBody()->write(json_encode(['error' => 'Accès refusé au détail du rendez-vous']));
                }
                break;

            default:
                // Pour les autres routes, pas de vérification spécifique pour l'instant
                break;
        }

        return $next($request, $response);
    }
}
