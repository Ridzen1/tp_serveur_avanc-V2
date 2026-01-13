<?php

namespace toubilib\core\application\ports\api;

use toubilib\core\application\dto\PraticienDTO;

interface ServicePraticienInterface
{
    /**
     * Liste tous les praticiens avec filtres optionnels.
     *
     * @param string|null $specialite
     * @param string|null $ville
     * @return array
     */
    public function listerPraticiens(?string $specialite = null, ?string $ville = null): array;

    public function getDetailPraticien(string $id): ?PraticienDTO;
}