<?php

namespace toubilib\core\application\ports\api;

use toubilib\core\application\dto\RdvDTO;
use toubilib\core\application\dto\InputRendezVousDTO;
use toubilib\core\application\dto\CreerIndisponibiliteDTO;

interface ServiceRdvInterface {
    public function getRdvById(string $id): ?RdvDTO;
    public function listerCreneauxOccupes(string $praticienId, string $dateDebut, string $dateFin): array;

    public function creerRendezVous(InputRendezVousDTO $dto): string;
    public function annulerRendezVous(string $idRdv): void;
    public function honorerRendezVous(string $idRdv): void;
    public function marquerNonHonoreRendezVous(string $idRdv): void;
    public function getHistoriqueConsultations(string $patientId): array;
    public function creerIndisponibilite(CreerIndisponibiliteDTO $dto): void;
}