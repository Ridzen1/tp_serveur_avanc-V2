<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServiceRdvInterface;

class ListerConsultationsPatientAction
{
    private ServiceRdvInterface $serviceRdv;

    public function __construct(ServiceRdvInterface $serviceRdv)
    {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $patientId = $args['id'];

        try {
            $rdvs = $this->serviceRdv->getHistoriqueConsultations($patientId);

            $payload = json_encode([
                'type' => 'collection',
                'count' => count($rdvs),
                'consultations' => $rdvs
            ]);

            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}