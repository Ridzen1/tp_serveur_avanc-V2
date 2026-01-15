<?php

namespace toubilib\core\application\usecases;

use toubilib\core\application\ports\spi\repositoryInterfaces\PraticienRepositoryInterface;
use toubilib\core\application\dto\PraticienDTO;
use toubilib\core\domain\entities\praticien\MotifVisite;
use toubilib\core\domain\entities\praticien\MoyenPaiement;
use toubilib\core\application\ports\api\ServicePraticienInterface;

class ServicePraticien implements ServicePraticienInterface
{
    private PraticienRepositoryInterface $praticienRepository;

    public function __construct(PraticienRepositoryInterface $praticienRepository)
    {
        $this->praticienRepository = $praticienRepository;
    }

    public function listerPraticiens(?string $specialite = null, ?string $ville = null): array {
        
        $praticiens = $this->praticienRepository->findAll($specialite, $ville);

        return array_map(function ($praticien) {
            return new PraticienDTO(
                $praticien->getId(),
                $praticien->getNom(),
                $praticien->getPrenom(),
                $praticien->getVille(),
                $praticien->getEmail(),
                $praticien->getTelephone(),
                $praticien->getSpecialite(),
                $praticien->getStructureId(),
                $praticien->getRppsId(),
                $praticien->isOrganisation(),
                $praticien->isNouveauPatient(),
                $praticien->getTitre(),
                array_map(function ($motif) {
                    return ['id' => $motif->getId(), 'libelle' => $motif->getLibelle()];
                }, $praticien->getMotifsVisite()),
                array_map(function ($moyen) {
                    return ['id' => $moyen->getId(), 'libelle' => $moyen->getLibelle()];
                }, $praticien->getMoyensPaiement())
            );
        }, $praticiens);
    }

    public function getDetailPraticien(string $id): ?PraticienDTO
    {
        $praticien = $this->praticienRepository->findById($id);

        if ($praticien === null) {
            return null;
        }

        return new PraticienDTO(
            $praticien->getId(),
            $praticien->getNom(),
            $praticien->getPrenom(),
            $praticien->getVille(),
            $praticien->getEmail(),
            $praticien->getTelephone(),
            $praticien->getSpecialite(),
            $praticien->getStructureId(),
            $praticien->getRppsId(),
            $praticien->isOrganisation(),
            $praticien->isNouveauPatient(),
            $praticien->getTitre(),
            array_map(function ($motif) {
                return ['id' => $motif->getId(), 'libelle' => $motif->getLibelle()];
            }, $praticien->getMotifsVisite()),
            array_map(function ($moyen) {
                return ['id' => $moyen->getId(), 'libelle' => $moyen->getLibelle()];
            }, $praticien->getMoyensPaiement())
        );
    }
}
