<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MilpacProfile;
use App\Entity\Regiment\Section;
use App\Entity\RosterTypeEnum;
use App\Repository\MilpacProfileRepository;
use App\Repository\Regiment\BilletPositionRepository;

readonly class ChainOfCommandService
{
    public function __construct(
        private MilpacProfileRepository  $milpacProfileRepository,
        private BilletPositionRepository $billetPositionRepository,
    )
    {
    }

    public function getForProfile(MilpacProfile $profile): array
    {
        return $this->getForSection($profile->getPrimaryBilletAssignment()->getSection());
    }

    public function getForSection(Section $section): array
    {
        $platoon = $section->getPlatoon();
        $company = $platoon->getCompany();
        $battalion = $company->getBattalion();
        $filter = [
            "section" => $section->getId(),
            "platoon" => $platoon->getId(),
            "company" => $company->getId(),
            "battalion" => $battalion->getId(),
        ];
        $coc = [];
        foreach ([
                     "ASL" => [22, "section", null, null, null],
                     "SL" => [21, "section", null, null, null],
                     "PSG" => [18, "platoon", "HQ", null, null],
                     "PL" => [17, "platoon", "HQ", null, null],
                     "CO 1SG" => [20, "company", "HQ", "HQ", null],
                     "CO XO" => [14, "company", "HQ", "HQ", null],
                     "CO CO" => [16, "company", "HQ", "HQ", null],
                     "BN SGM" => [19, "battalion", "HQ", "HQ", "HQ"],
                     "BN XO" => [14, "battalion", "HQ", "HQ", "HQ"],
                     "BN CO" => [1, "battalion", "HQ", "HQ", "HQ"],
                 ] as $prefix => [$positionId, $unitFilterType, $sectionTitleFilter, $platoonTitleFilter, $companyTitleFilter]) {
            $query = $this->milpacProfileRepository->createQueryBuilder("entity")->setMaxResults(3);
            $query->where("entity.rosterType IN (:rosterType)")->setParameter("rosterType", RosterTypeEnum::COMBAT);
            $query->andWhere("{$unitFilterType}.id LIKE :{$unitFilterType}Id")->setParameter("{$unitFilterType}Id", $filter[$unitFilterType]);
            if ($sectionTitleFilter) {
                $query->andWhere("section.title LIKE :sectionTitle")->setParameter("sectionTitle", $sectionTitleFilter);
            }
            if ($platoonTitleFilter) {
                $query->andWhere("platoon.title LIKE :platoonTitle")->setParameter("platoonTitle", $platoonTitleFilter);
            }
            if ($companyTitleFilter) {
                $query->andWhere("company.title LIKE :companyTitle")->setParameter("companyTitle", $companyTitleFilter);
            }
            $query->andWhere("position.id LIKE :positionId")->setParameter("positionId", $positionId);
            $arr = $query->getQuery()->execute();
            $coc[$prefix] = [
                "billetPosition" => $this->billetPositionRepository->findOneBy(["id" => $positionId]),
                "milpacProfile" => $arr[0] ?? null,
            ];
        }
        // CSM: Command Sergeant Major Nexhex.A
        // COS: Lieutenant General Burgundy.C Esq.
        return $coc;
    }
}
