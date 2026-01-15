<?php
namespace toubilib\api\middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use toubilib\core\application\dto\InputRendezVousDTO;

// --- CHANGEMENT 1 : On utilise la nouvelle interface Service (HTTP) ---
use toubilib\core\application\ports\spi\PraticienServiceInterface; 

use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;

class ValidationRendezVousMiddleware
{
    // --- CHANGEMENT 2 : Propriété Service ---
    private PraticienServiceInterface $praticienService;
    
    private PatientRepositoryInterface $patientRepository;
    private RdvRepositoryInterface $rdvRepository;

    public function __construct(
        PraticienServiceInterface $praticienService, // <--- C'est ICI que ça bloquait avant
        PatientRepositoryInterface $patientRepository,
        RdvRepositoryInterface $rdvRepository
    ) {
        $this->praticienService = $praticienService;
        $this->patientRepository = $patientRepository;
        $this->rdvRepository = $rdvRepository;
    }

    public function __invoke(Request $request, \Psr\Http\Server\RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $response = new \Slim\Psr7\Response();

        // Contrôles de présence
        $required = ['praticien_id', 'patient_id', 'date_heure_debut', 'motif_visite', 'duree'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $response->getBody()->write(json_encode(['error' => "Champ $field manquant"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        // --- CHANGEMENT 3 : Appel via le Service HTTP (Retourne un tableau) ---
        try {
            $praticienData = $this->praticienService->getPraticienById($data['praticien_id']);
        } catch (\Exception $e) {
            $praticienData = null;
        }

        if (!$praticienData) {
            $response->getBody()->write(json_encode(['error' => "Le praticien n'existe pas ou service inaccessible."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Vérifier que le patient existe
        $patient = $this->patientRepository->findById($data['patient_id']);
        if (!$patient) {
            $response->getBody()->write(json_encode(['error' => "Le patient n'existe pas."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // --- CHANGEMENT 4 : Manipulation de Tableau (Array) au lieu d'Objet ---
        // On récupère la liste des motifs depuis le tableau JSON
        $listeMotifs = $praticienData['motifs_visite'] ?? $praticienData['motifs'] ?? [];
        $motifIds = array_map(fn($m) => (string)$m['id'], $listeMotifs);
        
        if (!in_array($data['motif_visite'], $motifIds)) {
            $response->getBody()->write(json_encode([
                'error' => "Le motif de visite n'est pas valide pour ce praticien.",
                'debug_motifIds' => $motifIds,
                'debug_motif_visite' => $data['motif_visite']
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Suite des validations (Horaires, DTO...)
        // (Tu peux garder le reste de ton code de validation de dates ici)
        
        // Création du DTO pour passer à la suite
        $dto = new InputRendezVousDTO(
            $data['praticien_id'],
            $data['patient_id'],
            $data['date_heure_debut'],
            $data['motif_visite'],
            intval($data['duree'])
        );
        $request = $request->withAttribute('inputRendezVousDTO', $dto);

        return $handler->handle($request);
    }
}