<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\ports\api\ServiceRdvInterface;

class AnnulerRDVAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $idRdv = $args['id'];
        try {
            $this->serviceRdv->annulerRendezVous($idRdv);
            $response->getBody()->write(json_encode(['message' => 'Rendez-vous annulé avec succès']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $status = ($e->getMessage() === "Le rendez-vous n'existe pas.") ? 404 : 400;
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
        }
    }
}