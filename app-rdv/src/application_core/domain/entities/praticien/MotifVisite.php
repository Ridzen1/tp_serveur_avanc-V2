<?php

namespace toubilib\core\domain\entities\praticien;

class MotifVisite {
    private int $id;
    private int $specialiteId;
    private string $libelle;

    public function __construct(int $id, int $specialiteId, string $libelle)
    {
        $this->id = $id;
        $this->specialiteId = $specialiteId;
        $this->libelle = $libelle;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSpecialiteId(): int
    {
        return $this->specialiteId;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }
}
