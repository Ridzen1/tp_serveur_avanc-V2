<?php

namespace toubilib\core\application\ports\api;

use toubilib\core\application\dto\AuthDTO;

interface ServiceAuthzInterface {
    public function canAccessAgendaPraticien(string $praticienId, AuthDTO $user): bool;
    public function canAccessRdvDetail(string $rdvId, AuthDTO $user): bool;
}
