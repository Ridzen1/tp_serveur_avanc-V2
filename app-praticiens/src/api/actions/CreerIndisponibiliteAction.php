<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\dto\CreerIndisponibiliteDTO;
use toubilib\core\application\ports\api\ServiceRdvInterface;

class CreerIndisponibiliteAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $praticienId = $args['id'] ?? null;
        $data = $request->getParsedBody();

        $dateDebut = $data['date_debut'] ?? null;
        $dateFin = $data['date_fin'] ?? null;

        if (!$praticienId || !$dateDebut || !$dateFin) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Missing required parameters (praticien_id, date_debut, date_fin)'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }

        try {
            $dto = new CreerIndisponibiliteDTO($praticienId, $dateDebut, $dateFin);
            $this->serviceRdv->creerIndisponibilite($dto);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Indisponibilité créée avec succès'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    }
}
