<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\core\application\ports\api\ServicePatientInterface;

class CreerPatientAction
{
    private ServicePatientInterface $servicePatient;

    public function __construct(ServicePatientInterface $servicePatient)
    {
        $this->servicePatient = $servicePatient;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $dto = $request->getAttribute('inputPatientDTO');

        try {

            $newId = $this->servicePatient->creerPatient($dto);

            $responseData = [
                'type' => 'resource',
                'patient_id' => $newId,
                'message' => 'Patient créé avec succès',
                'links' => [
                    'self' => '/patients/' . $newId
                ]
            ];

            $response->getBody()->write(json_encode($responseData));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);

        } catch (\Exception $e) {
            $payload = json_encode(['error' => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}