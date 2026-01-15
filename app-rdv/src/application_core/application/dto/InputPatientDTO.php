<?php

namespace toubilib\core\application\dto;
class InputPatientDTO
{
    public string $nom;
    public string $prenom;
    public string $telephone;
    public ?string $email;
    public ?string $adresse;
    public ?string $code_postal;
    public ?string $ville;
    public ?string $date_naissance;

    public function __construct(
        string $nom, 
        string $prenom, 
        string $telephone, 
        ?string $email = null,
        ?string $adresse = null,
        ?string $code_postal = null,
        ?string $ville = null,
        ?string $date_naissance = null
    ) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->telephone = $telephone;
        $this->email = $email;
        $this->adresse = $adresse;
        $this->code_postal = $code_postal;
        $this->ville = $ville;
        $this->date_naissance = $date_naissance;
    }
}