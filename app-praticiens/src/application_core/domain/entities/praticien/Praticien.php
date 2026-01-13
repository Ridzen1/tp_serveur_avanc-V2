<?php

namespace toubilib\core\domain\entities\praticien;

class Praticien {
    private string $id;
    private string $nom;
    private string $prenom;
    private string $ville;
    private string $email;
    private string $telephone;
    private string $specialite;
    private ?string $structureId;
    private ?string $rppsId;
    private bool $organisation;
    private bool $nouveauPatient;
    private string $titre;
    private array $motifsVisite;
    private array $moyensPaiement;

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
        string $titre = 'Dr.'
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
        $this->motifsVisite = [];
        $this->moyensPaiement = [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getVille(): string
    {
        return $this->ville;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function getSpecialite(): string
    {
        return $this->specialite;
    }

    public function getStructureId(): ?string
    {
        return $this->structureId;
    }

    public function getRppsId(): ?string
    {
        return $this->rppsId;
    }

    public function isOrganisation(): bool
    {
        return $this->organisation;
    }

    public function isNouveauPatient(): bool
    {
        return $this->nouveauPatient;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getMotifsVisite(): array
    {
        return $this->motifsVisite;
    }

    public function getMoyensPaiement(): array
    {
        return $this->moyensPaiement;
    }

    public function addMotifVisite(MotifVisite $motif): void
    {
        $this->motifsVisite[] = $motif;
    }

    public function addMoyenPaiement(MoyenPaiement $moyen): void
    {
        $this->moyensPaiement[] = $moyen;
    }
}
