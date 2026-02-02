<?php
namespace toubilib\core\application\ports\api;

use toubilib\core\application\dto\InputPatientDTO; 

interface ServicePatientInterface
{
    public function creerPatient(InputPatientDTO $dto): string;
}