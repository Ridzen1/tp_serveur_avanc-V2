<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\ports\api\ServiceRdvInterface;

class ListerCreneauxOccupes
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $praticienId = $args['id'] ?? null;
        $params = $request->getQueryParams();
        $dateDebut = $params['dateDebut'] ?? null;
        $dateFin = $params['dateFin'] ?? null;

        if (!$praticienId || !$dateDebut || !$dateFin) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Missing required parameters'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        $creneaux = $this->serviceRdv->listerCreneauxOccupes($praticienId, $dateDebut, $dateFin);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $creneaux
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}