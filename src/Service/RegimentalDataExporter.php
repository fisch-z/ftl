<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MilpacProfile;
use App\Entity\MilpacProfileAward;
use App\Entity\RosterTypeEnum;
use App\Repository\MilpacProfileRepository;
use App\Repository\Regiment\BilletAssignmentRepository;

class RegimentalDataExporter
{
    private array $allRosterTypes = [
        RosterTypeEnum::COMBAT,
        RosterTypeEnum::RESERVE,
        RosterTypeEnum::ELOA,
        RosterTypeEnum::WALL_OF_HONOR,
        RosterTypeEnum::ARLINGTON,
        RosterTypeEnum::PAST_MEMBERS,
    ];
    private array $defaultRosterTypes = [
        RosterTypeEnum::COMBAT,
        RosterTypeEnum::RESERVE,
        RosterTypeEnum::ELOA,
    ];

    public function __construct(
        private readonly MilpacProfileRepository    $milpacProfileRepository,
        private readonly BilletAssignmentRepository $billetAssignmentRepository,
    )
    {
    }


    public function s1ApiMasterRawMilpacs(): \Generator
    {
        foreach ($this->milpacProfileRepository->findBy(["rosterType" => $this->allRosterTypes]) as $milpacProfile) {
            $billetAssignmentToRow = static function ($milpacProfile, $primary, $secondary) {
                $promotionDate = strtotime($milpacProfile->getData()["promotionDate"]);
                return [
                    "User Userid" => $milpacProfile->getUserId(),
                    "User Username" => $milpacProfile->getUsername(),
                    "Rank Rankshort" => $milpacProfile->getRank()->getTitleShort(),
                    "Rank Rankfull" => $milpacProfile->getRank()->getTitle(),
                    "Rank Rankimageurl" => $milpacProfile->getRank()->getRankImageUrl(),
                    "Realname" => $milpacProfile->getData()["realName"],
                    "Uniformurl" => $milpacProfile->getData()["uniformUrl"],
                    "Roster" => $milpacProfile->getRosterType(),
                    "Primary Positiontitle" => $primary->getMilpacTitle(),
                    "Secondaries" => "",
                    "Joindate" => date('Y-m-d', strtotime($milpacProfile->getData()["joinDate"])),
                    "Promotiondate" => $promotionDate ? date('Y-m-d', $promotionDate) : null,
                    "Secondaries Positiontitle" => $secondary ? $secondary->getMilpacTitle() : "",
                ];
            };
            $primary = $milpacProfile->getPrimaryBilletAssignment();
            $all = $milpacProfile->getBilletAssignments();
            if (count($all) === 1) {
                yield $billetAssignmentToRow($milpacProfile, $primary, "");
            } else {
                foreach ($all as $secondary) {
                    if ($primary->getId() !== $secondary->getId()) {
                        yield $billetAssignmentToRow($milpacProfile, $primary, $secondary);
                    }
                }
            }
        }
    }

    public function s1ApiOperationsProfiles(): \Generator
    {
        foreach ($this->milpacProfileRepository->findBy(["rosterType" => $this->defaultRosterTypes]) as $milpacProfile) {
            yield [
                "Userid" => $milpacProfile->getUserId(),
                "Username" => $milpacProfile->getUsername(),
                "Rank" => $milpacProfile->getRank()->getTitleShort(),
                "Roster" => $milpacProfile->getRosterType(),
                "Section" => $milpacProfile->getPrimaryBilletAssignment()->getSection()->getTitleFull(),
                "Battalion" => "'" . $milpacProfile->getPrimaryBilletAssignment()->getBattalion()->getTitle(),
                "Position" => $milpacProfile->getPrimaryBilletAssignment()->getPosition()->getTitle(),
                "ServiceBranch" => $milpacProfile->getPrimaryBilletAssignment()->getServiceBranch(),
            ];
        }
    }

    public function s1ApiOperationsAwards($battalion = null): \Generator
    {
        $query = $this->milpacProfileRepository->createQueryBuilder("entity");
        $query->where("entity.rosterType IN (:rosterType)")->setParameter("rosterType", $this->defaultRosterTypes);
        if ($battalion) {
            $query->andWhere("battalion.title LIKE :battalion")->setParameter("battalion", $battalion);
        }
        foreach ($query->getQuery()->execute() as $milpacProfile) {
            $insertRow = static function (MilpacProfile $milpacProfile, MilpacProfileAward $award, $row) {
                return [
                    "Userid" => $milpacProfile->getUserId(),
                    "Username" => $milpacProfile->getUsername(),
                    "Category" => $award->getCategory(),
                    "Name" => $award->getName(),
                    "OriginalName" => $row["originalAwardName"],
                    "WithValor" => $row["withValor"],
                    "Date" => $row["date"],
                    "Details" => $row["details"],

                ];
            };
            foreach ($milpacProfile->getAwardList()->getRibbons() as $award) {
                foreach ($award->getAllRows() as $row) {
                    yield $insertRow($milpacProfile, $award, $row);
                }
            }
            foreach ($milpacProfile->getAwardList()->getMedals() as $award) {
                foreach ($award->getAllRows() as $row) {
                    yield $insertRow($milpacProfile, $award, $row);
                }
            }
        }
    }

    public function s1ApiOperationsOperationsRecords($battalion = null, $onlyLatest = true): \Generator
    {
        $query = $this->milpacProfileRepository->createQueryBuilder("entity");
        $query->where("entity.rosterType IN (:rosterType)")->setParameter("rosterType", $this->defaultRosterTypes);
        if ($battalion) {
            $query->andWhere("battalion.title LIKE :battalion")->setParameter("battalion", $battalion);
        }
        foreach ($query->getQuery()->execute() as $milpacProfile) {
            $insertRow = static function (MilpacProfile $milpacProfile, $row) {
                return [
                    "Userid" => $milpacProfile->getUserId(),
                    "Username" => $milpacProfile->getUsername(),
                    "details" => $row["recordDetails"],
                    "date" => $row["recordDate"],
                ];
            };

            $records = $milpacProfile->getData()["records"];
            usort($records, fn($a, $b) => strtotime($a["recordDate"]) <=> strtotime($b["recordDate"]));
            foreach (array_reverse($records) as $row) {
                if ($row["recordType"] === "RECORD_TYPE_OPERATION") {
                    $rows[] = $insertRow($milpacProfile, $row);
                    if ($onlyLatest) {
                        break;
                    }
                }
            }
        }

        usort($rows, fn($a, $b) => strtotime($a["date"]) <=> strtotime($b["date"]));
        yield from $rows;
    }

    public function billetAssignmentsCsv(): \Generator
    {
        foreach ($this->billetAssignmentRepository->findAll() as $billetAssigment) {
            $row["milpacId"] = $billetAssigment->getMilpacId();
            $row["milpacTitle"] = $billetAssigment->getMilpacTitle();
            $row["positionTitle"] = $billetAssigment->getPosition()->getTitle();
            $section = $billetAssigment->getSection();
            $row["sectionFullTitle"] = $section->getTitleFull();
            $row["sectionTitle"] = $section->getTitleFull();
            $row["sectionCustomName"] = $section->getCustomName();
            $row["sectionServiceBranch"] = $section->getServiceBranch()->getTitle();
            $platoon = $section->getPlatoon();
            $row["platoonTitle"] = $platoon->getTitle();
            $row["platoonCustomName"] = $platoon->getCustomName();
            $company = $platoon->getCompany();
            $row["companyTitle"] = $company->getTitle();
            $row["companyCustomName"] = $company->getCustomName();
            $battalion = $company->getBattalion();
            $row["battalionTitle"] = $battalion->getTitle();
            $row["battalionCustomName"] = $battalion->getCustomName();
            yield $row;
        }
    }


    public function profiles($battalion = null, $company = null, $rosterTypes = null): \Generator
    {
        $allowedRosterTypes = $rosterTypes === null ? $this->defaultRosterTypes : array_intersect($rosterTypes, $this->allRosterTypes);
        $query = $this->milpacProfileRepository->createQueryBuilder("entity");
        $query->where("entity.rosterType IN (:rosterType)")->setParameter("rosterType", $allowedRosterTypes);
        if ($battalion) {
            $query->andWhere("battalion.title LIKE :battalion")->setParameter("battalion", $battalion);
        }
        if ($company) {
            $query->andWhere("company.title LIKE :company")->setParameter("company", $company);
        }
        foreach ($query->getQuery()->execute() as $milpacProfile) {
            /** @var MilpacProfile $milpacProfile */
            $primary = $milpacProfile->getPrimaryBilletAssignment();
            $promotionDate = strtotime($milpacProfile->getData()["promotionDate"]);
            yield [
                "Roster" => $milpacProfile->getRosterType(),
                "Userid" => $milpacProfile->getUserId(),
                "Username" => $milpacProfile->getUsername(),
                "Rank" => $milpacProfile->getRank()->getTitleShort(),
                "Position" => $primary->getPosition()->getTitle(),
                "Section" => $primary->getSection()->getTitleFull(),
                "Platoon" => $primary->getPlatoon()->getTitleFull(),
                "Company" => $primary->getCompany()->getTitleFull(),
                "Battalion" => $primary->getBattalion()->getTitle(),
                "ServiceBranch" => $primary->getServiceBranch(),
                "RankId" => $milpacProfile->getRank()->getTitleShort(),
                "RankFull" => $milpacProfile->getRank()->getTitle(),
                "RankImageUrl" => $milpacProfile->getRank()->getRankImageUrl(),
                "Realname" => $milpacProfile->getData()["realName"],
                "UniformUrl" => $milpacProfile->getData()["uniformUrl"],
                "JoinDate" => date('Y-m-d', strtotime($milpacProfile->getData()["joinDate"])),
                "PromotionDate" => $promotionDate ? date('Y-m-d', $promotionDate) : null,
                "ForumProfileId" => $milpacProfile->getForumProfileId(),
                "ForumProfileLink" => $milpacProfile->getForumProfileLink(),
                "hasNcoa" => (int)$milpacProfile->hasNcoa(),
                "hasOds" => (int)$milpacProfile->hasOds(),
                "hasSac" => (int)$milpacProfile->hasSac(),
            ];
        }
    }
}
