<?php
namespace toubilib\core\application\ports\spi\repositoryInterfaces;

use toubilib\core\domain\entities\rdv\RDV;

interface RdvRepositoryInterface {
    public function findById(string $id): ?RDV;
    public function findCreneauxByPraticienAndPeriode(string $praticienId, ?string $dateDebut, ?string $dateFin): array;
    public function save(RDV $rdv): void;
    public function findRdvsByPatientId(string $patientId): array;
}