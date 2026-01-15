<?php
namespace toubilib\core\application\usecases;


use toubilib\core\application\ports\api\ServicePatientInterface;
use toubilib\core\application\dto\InputPatientDTO;
use PDO;
use Ramsey\Uuid\Uuid;
class ServicePatient implements ServicePatientInterface{
    private PDO $pdo;

public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    }

    public function creerPatient(InputPatientDTO $dto): string
    {
        $uuid = Uuid::uuid4()->toString(); 

        $sql = "INSERT INTO patient (id, nom, prenom, telephone, email, adresse, code_postal, ville, date_naissance) 
                VALUES (:id, :nom, :prenom, :tel, :email, :adr, :cp, :ville, :dnaiss)";

        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute([
                'id' => $uuid,
                'nom' => $dto->nom,
                'prenom' => $dto->prenom,
                'tel' => $dto->telephone,
                'email' => $dto->email,
                'adr' => $dto->adresse,
                'cp' => $dto->code_postal,
                'ville' => $dto->ville,
                'dnaiss' => $dto->date_naissance
            ]);
        } catch (\PDOException $e) {
            throw new \Exception("Erreur lors de la création du patient : " . $e->getMessage());
        }

        return $uuid;
    }
}