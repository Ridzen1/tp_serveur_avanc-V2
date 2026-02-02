<?php

namespace toubilib\infra\repositories;

use toubilib\core\application\ports\spi\repositoryInterfaces\PatientRepositoryInterface;
use toubilib\core\domain\entities\patient\Patient;

class PDOPatientRepository implements PatientRepositoryInterface
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findById(string $id): ?Patient
    {
        $stmt = $this->pdo->prepare('SELECT * FROM patient WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return new Patient(
            $row['id'],
            $row['nom'],
            $row['prenom'],
            $row['date_naissance'],
            $row['adresse'],
            $row['code_postal'],
            $row['ville'],
            $row['email'],
            $row['telephone']
        );
    }
}
