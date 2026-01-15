<?php

namespace toubilib\core\domain\entities\rdv;

class RDV {
    public const STATUS_ANNULE = 0;
    public const STATUS_PLANIFIE = 1;
    public const STATUS_INDISPONIBLE = 2;
    public const STATUS_HONORE = 3;
    public const STATUS_NON_HONORE = 4;

    public const PATIENT_ID_INDISPONIBLE = 'SYSTEM_INDISPONIBILITE';

    private string $id;
    private string $praticien_id;
    private string $patient_id;
    private ?string $patient_email;
    private string $date_heure_debut;
    private string $date_heure_fin;
    private int $status;
    private int $duree;
    private string $date_creation;
    private string $motif_visite;

    public function __construct(
        string $id,
        string $praticien_id,
        string $patient_id,
        ?string $patient_email,
        string $date_heure_debut,
        string $date_heure_fin,
        int $status,
        int $duree,
        string $date_creation,
        string $motif_visite
    ) {
        $this->id = $id;
        $this->praticien_id = $praticien_id;
        $this->patient_id = $patient_id;
        $this->patient_email = $patient_email;
        $this->date_heure_debut = $date_heure_debut;
        $this->date_heure_fin = $date_heure_fin;
        $this->status = $status;
        $this->duree = $duree;
        $this->date_creation = $date_creation;
        $this->motif_visite = $motif_visite;
    }

    public function getId(): string { return $this->id; }
    public function getPraticienId(): string { return $this->praticien_id; }
    public function getPatientId(): string { return $this->patient_id; }
    public function getPatientEmail(): ?string { return $this->patient_email; }
    public function getDateHeureDebut(): string { return $this->date_heure_debut; }
    public function getDateHeureFin(): string { return $this->date_heure_fin; }
    public function getStatus(): int { return $this->status; }
    public function getDuree(): int { return $this->duree; }
    public function getDateCreation(): string { return $this->date_creation; }
    public function getMotifVisite(): string { return $this->motif_visite; }

    public function annuler(): void {

        if ($this->status === 0) {
            throw new \Exception("Le rendez-vous est déjà annulé.");
        }

        $now = new \DateTime();
        $debut = new \DateTime($this->date_heure_debut);

        if ($now > $debut) {
            throw new \Exception("Impossible d'annuler un rendez-vous dont la dâte est déjà passée.");
        }

        $this->status = 0; // Annulé
    }

    public function honorer(): void {
        if ($this->status === self::STATUS_ANNULE) {
            throw new \Exception("Impossible d'honorer un rendez-vous annulé.");
        }

        if ($this->status === self::STATUS_INDISPONIBLE) {
            throw new \Exception("Impossible d'honorer une période d'indisponibilité.");
        }

        if ($this->status === self::STATUS_HONORE) {
            throw new \Exception("Le rendez-vous est déjà honoré.");
        }

        if ($this->status === self::STATUS_NON_HONORE) {
            throw new \Exception("Le rendez-vous est déjà marqué comme non honoré.");
        }

        $now = new \DateTime();
        $fin = new \DateTime($this->date_heure_fin);

        if ($now < $fin) {
            throw new \Exception("Impossible d'honorer un rendez-vous dont la date de fin n'est pas encore passée.");
        }

        $this->status = self::STATUS_HONORE;
    }

    public function marquerNonHonore(): void {
        if ($this->status === self::STATUS_ANNULE) {
            throw new \Exception("Impossible de marquer comme non honoré un rendez-vous annulé.");
        }

        if ($this->status === self::STATUS_INDISPONIBLE) {
            throw new \Exception("Impossible de marquer une période d'indisponibilité comme non honorée.");
        }

        if ($this->status === self::STATUS_HONORE) {
            throw new \Exception("Le rendez-vous est déjà honoré.");
        }

        if ($this->status === self::STATUS_NON_HONORE) {
            throw new \Exception("Le rendez-vous est déjà marqué comme non honoré.");
        }

        $now = new \DateTime();
        $debut = new \DateTime($this->date_heure_debut);

        if ($now < $debut) {
            throw new \Exception("Impossible de marquer comme non honoré un rendez-vous dont la date n'est pas encore passée.");
        }

        $this->status = self::STATUS_NON_HONORE;
    }
}