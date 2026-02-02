<?php

namespace toubilib\core\application\dto;

class CreerIndisponibiliteDTO
{
    public string $praticien_id;
    public string $date_debut;
    public string $date_fin;

    public function __construct(string $praticien_id, string $date_debut, string $date_fin)
    {
        $this->praticien_id = $praticien_id;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
    }
}
