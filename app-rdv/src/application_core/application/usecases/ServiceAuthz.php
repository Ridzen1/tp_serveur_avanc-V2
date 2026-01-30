<?php
declare(strict_types=1);

namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\spi\repositoryInterfaces\RdvRepositoryInterface;

class ServiceAuthz
{
    private RdvRepositoryInterface $rdvRepository;

    public function __construct(RdvRepositoryInterface $rdvRepository)
    {
        $this->rdvRepository = $rdvRepository;
    }

    /**
     * Vérifie si l'utilisateur peut accéder à l'agenda d'un praticien
     * Seul le praticien concerné peut accéder à son propre agenda
     */
    public function canAccessAgendaPraticien(string $praticienId, array $userData): bool
    {
        // Seul le praticien peut accéder à son propre agenda
        // L'ID du praticien doit correspondre à l'ID de l'utilisateur authentifié
        $userId = $userData['user_id'] ?? $userData['sub'] ?? null;
        
        if (!$userId) {
            return false;
        }

        return $praticienId === $userId;
    }

    /**
     * Vérifie si l'utilisateur peut accéder au détail d'un RDV
     * Le patient ou le praticien concerné peuvent accéder au RDV
     */
    public function canAccessRdvDetail(string $rdvId, array $userData): bool
    {
        $userId = $userData['user_id'] ?? $userData['sub'] ?? null;
        
        if (!$userId) {
            return false;
        }

        // Récupérer le RDV
        $rdv = $this->rdvRepository->findById($rdvId);
        
        if (!$rdv) {
            return false;
        }

        // L'utilisateur doit être soit le patient, soit le praticien du RDV
        return $rdv->getPraticienId() === $userId || $rdv->getPatientId() === $userId;
    }

    /**
     * Vérifie si l'utilisateur peut créer un RDV
     * Le praticien ou le patient concerné peuvent créer le RDV
     */
    public function canCreateRdv(string $praticienId, string $patientId, array $userData): bool
    {
        $userId = $userData['user_id'] ?? $userData['sub'] ?? null;
        
        if (!$userId) {
            return false;
        }

        // L'utilisateur doit être soit le praticien, soit le patient du RDV
        return $praticienId === $userId || $patientId === $userId;
    }
}
