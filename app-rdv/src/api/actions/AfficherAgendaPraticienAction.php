<?php
namespace toubilib\api\actions;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;
use toubilib\core\application\usecases\ServiceRdv;

class AfficherAgendaPraticienAction {
    private ServiceRdv $serviceRdv;
    public function __construct(ServiceRdv $serviceRdv) {
        $this->serviceRdv = $serviceRdv;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $praticienId = $args['id'] ?? null;
        $queryParams = $request->getQueryParams();
        $dateDebut = $queryParams['date_debut'] ?? null;
        $dateFin = $queryParams['date_fin'] ?? null;

        $agenda = $this->serviceRdv->getAgendaPraticien($praticienId, $dateDebut, $dateFin);

        $response = new Response();
        $response->getBody()->write(json_encode($agenda));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
