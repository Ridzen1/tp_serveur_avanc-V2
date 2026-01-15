<?php
namespace toubilib\infra\repositories;

use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\core\domain\entities\rdv\RDV;
use PDO;

class PDORdvRepository implements RdvRepositoryInterface {
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function findById(string $id): ?RDV {
        $stmt = $this->pdo->prepare('SELECT * FROM rdv WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        return new RDV(
            $row['id'],
            $row['praticien_id'],
            $row['patient_id'],
            $row['patient_email'],
            $row['date_heure_debut'],
            $row['date_heure_fin'],
            $row['status'],
            $row['duree'],
            $row['date_creation'],
            $row['motif_visite']
        );
    }

    public function findCreneauxByPraticienAndPeriode(string $praticienId, string $dateDebut, string $dateFin): array {
        $stmt = $this->pdo->prepare('SELECT * FROM rdv WHERE praticien_id = :pid AND date_heure_debut >= :deb AND date_heure_fin <= :fin');
        $stmt->execute(['pid' => $praticienId, 'deb' => $dateDebut, 'fin' => $dateFin]);
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[] = new RDV(
                $row['id'],
                $row['praticien_id'],
                $row['patient_id'],
                $row['patient_email'],
                $row['date_heure_debut'],
                $row['date_heure_fin'],
                $row['status'],
                $row['duree'],
                $row['date_creation'],
                $row['motif_visite']
            );
        }
        return $result;
    }

    public function save(RDV $rdv): void {          // sert à la fois pour créer et mettre à jour (upsert)
        // Vérifie si le rdv existe déjà (par id)
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM rdv WHERE id = :id');
        $stmt->execute(['id' => $rdv->getId()]);
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            // Mise à jour (ex: annulation = status à 0)
            $stmt = $this->pdo->prepare('
                UPDATE rdv SET
                    praticien_id = :praticien_id,
                    patient_id = :patient_id,
                    patient_email = :patient_email,
                    date_heure_debut = :date_heure_debut,
                    date_heure_fin = :date_heure_fin,
                    status = :status,
                    duree = :duree,
                    date_creation = :date_creation,
                    motif_visite = :motif_visite
                WHERE id = :id
            ');
        } else {
            // Insertion
            $stmt = $this->pdo->prepare('
                INSERT INTO rdv (id, praticien_id, patient_id, patient_email, date_heure_debut, date_heure_fin, status, duree, date_creation, motif_visite)
                VALUES (:id, :praticien_id, :patient_id, :patient_email, :date_heure_debut, :date_heure_fin, :status, :duree, :date_creation, :motif_visite)
            ');
        }

        $stmt->execute([
            'id' => $rdv->getId(),
            'praticien_id' => $rdv->getPraticienId(),
            'patient_id' => $rdv->getPatientId(),
            'patient_email' => $rdv->getPatientEmail(),
            'date_heure_debut' => $rdv->getDateHeureDebut(),
            'date_heure_fin' => $rdv->getDateHeureFin(),
            'status' => $rdv->getStatus(),
            'duree' => $rdv->getDuree(),
            'date_creation' => $rdv->getDateCreation(),
            'motif_visite' => $rdv->getMotifVisite()
        ]);
    }

    public function findRdvsByPatientId(string $patientId): array
    {
        // On récupère tous les champs (*) de la table RDV pour ce patient
        $stmt = $this->pdo->prepare("SELECT * FROM rdv WHERE patient_id = :pid ORDER BY date_heure_debut DESC");
        $stmt->execute(['pid' => $patientId]);

        $results = $stmt->fetchAll();
        $rdvs = [];

        foreach ($results as $row) {
            $rdvs[] = new RDV(
                $row['id'],
                $row['praticien_id'],
                $row['patient_id'],
                $row['patient_email'] ?? '',
                $row['date_heure_debut'],
                $row['date_heure_fin'],
                $row['status'] ?? 1,
                $row['duree'],
                $row['date_creation'] ?? date('Y-m-d H:i:s'),
                $row['type_document'] ?? 'Consultation'
            );
        }
        return $rdvs;
    }

}