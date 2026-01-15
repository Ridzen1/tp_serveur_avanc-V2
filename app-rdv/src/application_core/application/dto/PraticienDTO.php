<?php

namespace toubilib\core\application\dto;

class PraticienDTO {
    public string $id;
    public string $nom;
    public string $prenom;
    public string $ville;
    public string $email;
    public string $telephone;
    public string $specialite;
    public ?string $structureId;
    public ?string $rppsId;
    public bool $organisation;
    public bool $nouveauPatient;
    public string $titre;
    public array $motifsVisite;
    public array $moyensPaiement;
    public ?string $nomStructure;
    public ?string $adresseStructure;
    public ?string $codePostalStructure;
    public ?string $telephoneStructure;

    public function __construct(
        string $id,
        string $nom,
        string $prenom,
        string $ville,
        string $email,
        string $telephone,
        string $specialite,
        ?string $structureId = null,
        ?string $rppsId = null,
        bool $organisation = false,
        bool $nouveauPatient = true,
        string $titre = 'Dr.',
        array $motifsVisite = [],
        array $moyensPaiement = [],
        ?string $nomStructure = null,
        ?string $adresseStructure = null,
        ?string $codePostalStructure = null,
        ?string $telephoneStructure = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->ville = $ville;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->specialite = $specialite;
        $this->structureId = $structureId;
        $this->rppsId = $rppsId;
        $this->organisation = $organisation;
        $this->nouveauPatient = $nouveauPatient;
        $this->titre = $titre;
        $this->motifsVisite = $motifsVisite;
        $this->moyensPaiement = $moyensPaiement;
        $this->nomStructure = $nomStructure;
        $this->adresseStructure = $adresseStructure;
        $this->codePostalStructure = $codePostalStructure;
        $this->telephoneStructure = $telephoneStructure;
    }
}
