<?php
namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\ports\api\ServiceRdvInterface;

class CreerRendezVousAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // Récupère le DTO créé par le middleware
        $dto = $request->getAttribute('inputRendezVousDTO');
        if (!$dto) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Données invalides ou manquantes'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Appelle le service métier pour créer le RDV
            $rdvId = $this->serviceRdv->creerRendezVous($dto);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Rendez-vous créé',
                'rdv_id' => $rdvId,
                'links' => [
                    'self' => '/rdvs/' . $rdvId
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
}