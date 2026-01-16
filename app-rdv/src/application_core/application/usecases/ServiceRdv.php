<?php
namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
// RETIRÉ : use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
// AJOUTÉ : L'interface que tu as créée pour l'adaptateur
use toubilib\core\application\ports\spi\PraticienServiceInterface; 

use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\dto\InputRendezVousDTO;
use toubilib\core\application\dto\CreerIndisponibiliteDTO;
use toubilib\core\domain\entities\rdv\RDV;
use toubilib\core\application\ports\api\ServiceRdvInterface;
use Ramsey\Uuid\Uuid;

class ServiceRdv implements ServiceRdvInterface
{
    private RdvRepositoryInterface $rdvRepository;
    // MODIFIÉ : On utilise le Service (Interface) au lieu du Repository
    private PraticienServiceInterface $praticienService;
    private PatientRepositoryInterface $patientRepository;

    public function __construct(
        RdvRepositoryInterface $rdvRepository,
        PraticienServiceInterface $praticienService, // Injection de l'adaptateur
        PatientRepositoryInterface $patientRepository
    ) {
        $this->rdvRepository = $rdvRepository;
        $this->praticienService = $praticienService;
        $this->patientRepository = $patientRepository;
    }

    public function getRdvById(string $id): ?RdvDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        if (!$rdv)
            return null;
        return new RdvDTO(
            $rdv->getId(),
            $rdv->getPraticienId(),
            $rdv->getPatientId(),
            $rdv->getPatientEmail(),
            $rdv->getDateHeureDebut(),
            $rdv->getDateHeureFin(),
            $rdv->getStatus(),
            $rdv->getDuree(),
            $rdv->getDateCreation(),
            $rdv->getMotifVisite()
        );
    }

    public function listerCreneauxOccupes(string $praticienId, string $dateDebut, string $dateFin): array {
        $rdvs = $this->rdvRepository->findCreneauxByPraticienAndPeriode($praticienId, $dateDebut, $dateFin);
        return array_map(function($rdv) {
            return new RdvDTO(
                $rdv->getId(),
                $rdv->getPraticienId(),
                $rdv->getPatientId(),
                $rdv->getPatientEmail(),
                $rdv->getDateHeureDebut(),
                $rdv->getDateHeureFin(),
                $rdv->getStatus(),
                $rdv->getDuree(),
                $rdv->getDateCreation(),
                $rdv->getMotifVisite()
            );
        }, $rdvs);
    }

    public function creerRendezVous(InputRendezVousDTO $dto): string {

        // --- MODIFICATION MAJEURE ICI ---
        // On récupère le praticien via l'API (HTTP)
        // Cela retourne un TABLEAU (array), pas un objet Entity
        try {
            $praticienData = $this->praticienService->getPraticienById($dto->praticien_id);
        } catch (\Exception $e) {
            throw new \Exception("Le praticien n'existe pas ou le service est inaccessible.");
        }

        if (!$praticienData) {
            throw new \Exception("Le praticien n'existe pas.");
        }

        // Vérifier que le patient existe
        $patient = $this->patientRepository->findById($dto->patient_id);
        if (!$patient) {
            throw new \Exception("Le patient n'existe pas.");
        }

        // --- ADAPTATION TABLEAU VS OBJET ---
        // $praticienData est un array, on suppose que la clé est 'motifs_visite' ou 'specialites'
        // Selon la structure JSON renvoyée par api.praticiens.
        // On va assumer que le JSON contient une clé 'motifs' ou 'motifs_visite'
        
        // Sécurisation : on cherche la liste des motifs dans la réponse
        $listeMotifs = $praticienData['motifs_visite'] ?? $praticienData['motifs'] ?? []; 
        
        $motifTrouve = false;
        $motifLabel = '';

        foreach ($listeMotifs as $motif) {
            // $motif est un tableau, on accède via ['id']
            if ((string)$motif['id'] === $dto->motif_visite) {
                $motifLabel = $motif['libelle'];
                $motifTrouve = true;
                break;
            }
        }

        if (!$motifTrouve) {
            throw new \Exception("Le motif de visite n'est pas valide pour ce praticien.");
        }

        // -----------------------------------

        $id = Uuid::uuid4()->toString();

        // Vérifier que le créneau horaire demandé est valide : jour ouvré et horaire possible
        $dateDebut = new \DateTime($dto->date_heure_debut);
        $dayOfWeek = (int)$dateDebut->format('N'); // 1 = Monday, 7 = Sunday
        if ($dayOfWeek > 5) {
            throw new \Exception("Le créneau doit être un jour ouvré (lundi à vendredi).");
        }
        $hour = (int)$dateDebut->format('H');
        if ($hour < 8 || $hour >= 19) {
            throw new \Exception("L'horaire doit être entre 8h et 19h.");
        }

        // Vérifier que le praticien est disponible pour le créneau horaire demandé
        $dateFin = clone $dateDebut;
        $dateFin->add(new \DateInterval('PT' . $dto->duree . 'M'));
        $dateDebutStr = $dateDebut->format('Y-m-d') . ' 00:00:00';
        $dateFinStr = $dateDebut->format('Y-m-d') . ' 23:59:59';
        
        $existingRdvs = $this->rdvRepository->findCreneauxByPraticienAndPeriode($dto->praticien_id, $dateDebutStr, $dateFinStr);
        foreach ($existingRdvs as $existingRdv) {
            $existingDebut = new \DateTime($existingRdv->getDateHeureDebut());
            $existingFin = new \DateTime($existingRdv->getDateHeureFin());
            if ($dateDebut < $existingFin && $dateFin > $existingDebut) {
                throw new \Exception("Le praticien n'est pas disponible pour ce créneau.");
            }
        }

        $id = uniqid();
        $date_heure_fin = $dateFin->format('Y-m-d H:i:s');
        $status = 1; // Assuming 1 for active/confirmed
        $date_creation = date('Y-m-d H:i:s');
        $patient_email = $patient->getEmail();

        $rdv = new RDV(
            $id,
            $dto->praticien_id,
            $dto->patient_id,
            $patient_email,
            $dto->date_heure_debut,
            $date_heure_fin,
            $status,
            $dto->duree,
            $date_creation,
            $motifLabel
        );

        $this->rdvRepository->save($rdv);
        return $id;
    }

    public function annulerRendezVous(string $idRdv): void
    {
        $rdv = $this->rdvRepository->findById($idRdv);
        if (!$rdv) {
            throw new \Exception("Le rendez-vous n'existe pas.");
        }
        $rdv->annuler();
        $this->rdvRepository->save($rdv);
    }

    public function honorerRendezVous(string $idRdv): void
    {
        $rdv = $this->rdvRepository->findById($idRdv);
        if (!$rdv) {
            throw new \Exception("Le rendez-vous n'existe pas.");
        }
        $rdv->honorer();
        $this->rdvRepository->save($rdv);
    }

    public function marquerNonHonoreRendezVous(string $idRdv): void
    {
        $rdv = $this->rdvRepository->findById($idRdv);
        if (!$rdv) {
            throw new \Exception("Le rendez-vous n'existe pas.");
        }
        $rdv->marquerNonHonore();
        $this->rdvRepository->save($rdv);
    }

    public function getAgendaPraticien(string $praticienId, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        // if (!$dateDebut || !$dateFin) { ... } removed to support unrestricted search
        // Permettre la recherche sur toute la période sans dates spécifiées

        $rdvs = $this->rdvRepository->findCreneauxByPraticienAndPeriode($praticienId, $dateDebut, $dateFin);

        $agenda = [];
        foreach ($rdvs as $rdv) {
            $item = [
                'id' => $rdv->getId(),
                'date' => $rdv->getDateHeureDebut(),
                'heure' => date('H:i', strtotime($rdv->getDateHeureDebut())),
                'duree' => $rdv->getDuree(),
                'motif' => $rdv->getMotifVisite(),
                'etat' => $rdv->getStatus(),
            ];

            if ($rdv->getStatus() === RDV::STATUS_INDISPONIBLE) {
                $item['type'] = 'indisponibilite';
            } else {
                $item['type'] = 'rdv';
                $item['patient'] = [
                    'id' => $rdv->getPatientId(),
                    'lien' => '/patients/' . $rdv->getPatientId()
                ];
            }
            $agenda[] = $item;
        }
        return $agenda;
    }

    public function creerIndisponibilite(CreerIndisponibiliteDTO $dto): void
    {
        $id = Uuid::uuid4()->toString();
        $dateDebut = new \DateTime($dto->date_debut);
        $dateFin = new \DateTime($dto->date_fin);
        
        $diff = $dateFin->diff($dateDebut);
        $minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        $rdv = new RDV(
            $id,
            $dto->praticien_id,
            RDV::PATIENT_ID_INDISPONIBLE,
            null, 
            $dto->date_debut,
            $dto->date_fin,
            RDV::STATUS_INDISPONIBLE,
            $minutes,
            date('Y-m-d H:i:s'), 
            'Indisponibilité' 
        );

        $this->rdvRepository->save($rdv);
    }

    public function getHistoriqueConsultations(string $patientId): array
    {
        $rdvs = $this->rdvRepository->findRdvsByPatientId($patientId);

        return array_map(function($rdv) {
            return new RdvDTO(
                $rdv->getId(),
                $rdv->getPraticienId(),
                $rdv->getPatientId(),
                $rdv->getPatientEmail(),
                $rdv->getDateHeureDebut(),
                $rdv->getDateHeureFin(),
                $rdv->getStatus(),
                $rdv->getDuree(),
                $rdv->getDateCreation(),
                $rdv->getMotifVisite()
            );
        }, $rdvs);
    }
}