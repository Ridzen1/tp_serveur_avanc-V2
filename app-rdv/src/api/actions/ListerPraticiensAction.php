<?php

namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use toubilib\core\application\ports\api\ServicePraticienInterface;

class ListerPraticiensAction
{
    private ServicePraticienInterface $servicePraticien;

    public function __construct(ServicePraticienInterface $servicePraticien)
    {
        $this->servicePraticien = $servicePraticien;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // 1. On récupère les paramètres de l'URL
        $queryParams = $request->getQueryParams();
        $specialite = $queryParams['specialite'] ?? null;
        $ville = $queryParams['ville'] ?? null;

        // 2. On appelle le service avec ces paramètres
        $praticiens = $this->servicePraticien->listerPraticiens($specialite, $ville);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $praticiens
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}