<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\dto\AuthDTO;
use toubilib\core\application\ports\api\ServiceAuthzInterface;
use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;
use toubilib\core\domain\entities\rdv\RDV;

class ServiceAuthz implements ServiceAuthzInterface
{
    private RdvRepositoryInterface $rdvRepository;

    public function __construct(RdvRepositoryInterface $rdvRepository)
    {
        $this->rdvRepository = $rdvRepository;
    }

    public function canAccessAgendaPraticien(string $praticienId, AuthDTO $user): bool
    {
        // Seuls les praticiens peuvent accéder à l'agenda d'un praticien
        if ($user->role !== '10') {
            return false;
        }

        // Un praticien peut accéder à son propre agenda
        return $user->id === $praticienId;
    }

    public function canAccessRdvDetail(string $rdvId, AuthDTO $user): bool
    {
        $rdv = $this->rdvRepository->findById($rdvId);
        if (!$rdv) {
            return false;
        }


        $role = (int) $user->role;
        
        // si role praticien (=10) propriétaire OU si role patient (=1) propriétaire
        // donc praticiens identifiés peuvent accéder aux RDV leur agenda 
        // OU patients identifiés peuvent accéder à leurs propres RDVs
        return ($role === 10 && $rdv->getPraticienId() === $user->id)
            || ($role === 1  && $rdv->getPatientId() === $user->id);
        
        
    }
}
