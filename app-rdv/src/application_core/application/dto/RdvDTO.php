<?php
namespace toubilib\core\application\dto;

class RdvDTO {
    public string $id;
    public string $praticien_id;
    public string $patient_id;
    public ?string $patient_email;
    public string $date_heure_debut;
    public string $date_heure_fin;
    public int $status;
    public int $duree;
    public string $date_creation;
    public string $motif_visite;

    public function __construct($id, $praticien_id, $patient_id, $patient_email, $date_heure_debut, $date_heure_fin, $status, $duree, $date_creation, $motif_visite) {
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
}