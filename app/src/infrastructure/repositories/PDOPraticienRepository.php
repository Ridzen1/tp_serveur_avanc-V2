<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\domain\entities\praticien\Praticien;
use toubilib\core\domain\entities\praticien\MotifVisite;
use toubilib\core\domain\entities\praticien\MoyenPaiement;

class PDOPraticienRepository implements PraticienRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Modification : Paramètres ajoutés et SQL dynamique
    public function findAll(?string $specialite = null, ?string $ville = null): array {
        
        // 1. La requête de base avec la Jointure
        $sql = 'SELECT p.*, s.libelle FROM praticien p JOIN specialite s ON p.specialite_id = s.id';
        
        $conditions = [];
        $params = [];

        // 2. On ajoute les conditions si les paramètres sont présents
        if ($specialite !== null && $specialite !== '') {
            // On cherche dans la table 'specialite' (alias s)
            $conditions[] = "s.libelle ILIKE :specialite";
            $params['specialite'] = '%' . $specialite . '%';
        }

        if ($ville !== null && $ville !== '') {
            // On cherche dans la table 'praticien' (alias p)
            $conditions[] = "p.ville ILIKE :ville";
            $params['ville'] = '%' . $ville . '%';
        }

        // 3. On assemble le SQL
        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        // 4. Exécution préparée
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // 5. Mapping (Ton code existant inchangé)
        return array_map(function ($row) {
            $praticien = new Praticien(
                $row['id'],
                $row['nom'],
                $row['prenom'],
                $row['ville'],
                $row['email'],
                $row['telephone'],
                $row['libelle'],
                $row['structure_id'],
                $row['rpps_id'],
                (bool)$row['organisation'],
                (bool)$row['nouveau_patient'],
                $row['titre']
            );

            $this->loadMotifsVisite($praticien);
            $this->loadMoyensPaiement($praticien);

            return $praticien;
        }, $results);
    }

    // ... Le reste du fichier (findById, loadMotifs...) reste INCHANGÉ ...
    public function findById(string $id): ?Praticien
    {
        try {
            $stmt = $this->pdo->prepare('SELECT p.*, s.libelle FROM praticien p JOIN specialite s ON p.specialite_id = s.id WHERE p.id = :id');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row === false) {
                return null;
            }

            $praticien = new Praticien(
                $row['id'],
                $row['nom'],
                $row['prenom'],
                $row['ville'],
                $row['email'],
                $row['telephone'],
                $row['libelle'],
                $row['structure_id'],
                $row['rpps_id'],
                (bool)$row['organisation'],
                (bool)$row['nouveau_patient'],
                $row['titre']
            );

            $this->loadMotifsVisite($praticien);
            $this->loadMoyensPaiement($praticien);

            return $praticien;
        } catch (\PDOException $e) {
            return null;
        }
    }

    private function loadMotifsVisite(Praticien $praticien): void
    {
        // (Garde ton code actuel ici)
        $stmt = $this->pdo->prepare('
            SELECT mv.id, mv.libelle
            FROM motif_visite mv
            JOIN praticien2motif p2m ON mv.id = p2m.motif_id
            WHERE p2m.praticien_id = :praticien_id
        ');
        $stmt->execute(['praticien_id' => $praticien->getId()]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $motif = new MotifVisite($row['id'], 0, $row['libelle']);
            $praticien->addMotifVisite($motif);
        }
    }

    private function loadMoyensPaiement(Praticien $praticien): void
    {
        // (Garde ton code actuel ici)
        $stmt = $this->pdo->prepare('
            SELECT mp.id, mp.libelle
            FROM moyen_paiement mp
            JOIN praticien2moyen p2m ON mp.id = p2m.moyen_id
            WHERE p2m.praticien_id = :praticien_id
        ');
        $stmt->execute(['praticien_id' => $praticien->getId()]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $moyen = new MoyenPaiement($row['id'], $row['libelle']);
            $praticien->addMoyenPaiement($moyen);
        }
    }
}