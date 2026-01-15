<?php
namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\ports\api\ServiceRdvInterface;

class AfficherRdvAction {
    private ServiceRdvInterface $serviceRdv;
    public function __construct(ServiceRdvInterface $serviceRdv) {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(Request $request, Response $response, array $args): Response {
        $id = $args['id'] ?? null;
        if (!$id) {
            return $response->withStatus(400);
        }
        $rdv = $this->serviceRdv->getRdvById($id);
        if (!$rdv) {
            return $response->withStatus(404);
        }
        $response->getBody()->write(json_encode($rdv));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}