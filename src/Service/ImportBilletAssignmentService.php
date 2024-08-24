<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Regiment\Battalion;
use App\Entity\Regiment\BilletAssignment;
use App\Entity\Regiment\Company;
use App\Entity\Regiment\Platoon;
use App\Entity\Regiment\Section;
use App\Entity\Regiment\ServiceBranch;
use App\Repository\Regiment\BattalionRepository;
use App\Repository\Regiment\BilletAssignmentRepository;
use App\Repository\Regiment\CompanyRepository;
use App\Repository\Regiment\PlatoonRepository;
use App\Repository\Regiment\SectionRepository;
use App\Repository\Regiment\ServiceBranchRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportBilletAssignmentService
{
    public function __construct(
        private readonly BilletAssignmentRepository $billetAssignmentRepository,
        private readonly SectionRepository          $sectionRepository,
        private readonly PlatoonRepository          $platoonRepository,
        private readonly CompanyRepository          $companyRepository,
        private readonly BattalionRepository        $battalionRepository,
        private readonly ServiceBranchRepository    $serviceBranchRepository,
        private readonly EntityManagerInterface     $entityManager,
    )
    {
    }

    public function importFromFile()
    {
        $billetAssignmentRepository = $this->billetAssignmentRepository;
        $sectionRepository = $this->sectionRepository;
        $platoonRepository = $this->platoonRepository;
        $companyRepository = $this->companyRepository;
        $battalionRepository = $this->battalionRepository;
        $serviceBranchRepository = $this->serviceBranchRepository;

        $a = json_decode(file_get_contents(\BASE_PATH . "/public/import-billet-assignment-list.json"), true);

        $rows = [];
        foreach ($a as $id => $str) {
            $row = $this->parseBilletString($str);
            $row["milpacTitle"] = $str;
            $row["milpacId"] = $id;
            $row["position"] = $row["assignment"];
            $rows[] = $row;
        }

        foreach ($rows as $row) {
            $row["battalion"] = str_replace("Regimental", "Regimental HQ", $row["battalion"]);
            $fullSection = implode("/", [$row["section"], $row["platoon"], $row["company"], $row["battalion"]]);
            $row["serviceBranche"] = trim(match ($fullSection) {
                default => "",
                "Future Concepts Center/HQ/HQ/ACD" => "Infantry",
                "HQ/HQ/A/1-7" => "Aviation",
                "HQ/1/A/1-7" => "Aviation",
                "1/1/A/1-7" => "Aviation",
                "2/1/A/1-7" => "Aviation",
                "3/1/A/1-7" => "Aviation",
                "4/1/A/1-7" => "Aviation",
                "5/1/A/1-7" => "Aviation",
                "HQ/2/A/1-7" => "Aviation",
                "1/2/A/1-7" => "Aviation",
                "2/2/A/1-7" => "Aviation",
                "3/2/A/1-7" => "Aviation",
                "4/2/A/1-7" => "Aviation",
                "HQ/HQ/B/1-7" => "Medical",
                "HQ/1/B/1-7" => "Medical",
                "1/1/B/1-7" => "Medical",
                "2/1/B/1-7" => "Medical",
                "3/1/B/1-7" => "Medical",
                "4/1/B/1-7" => "Medical",
                "HQ/2/B/1-7" => "Infantry",
                "HQ/3/B/1-7" => "Infantry",
                "HQ/4/B/1-7" => "Infantry",
                "1/2/B/1-7" => "Infantry",
                "2/2/B/1-7" => "Infantry",
                "3/2/B/1-7" => "Infantry",
                "4/2/B/1-7" => "Infantry",
                "1/3/B/1-7" => "Infantry",
                "2/3/B/1-7" => "Infantry",
                "3/3/B/1-7" => "Infantry",
                "4/3/B/1-7" => "Infantry",
                "1/4/B/1-7" => "Infantry",
                "2/4/B/1-7" => "Infantry",
                "3/4/B/1-7" => "Infantry",
                "4/4/B/1-7" => "Infantry",
                "HQ/HQ/C/1-7" => "Infantry",
                "HQ/1/C/1-7" => "Infantry",
                "1/1/C/1-7" => "Infantry",
                "2/1/C/1-7" => "Infantry",
                "3/1/C/1-7" => "Infantry",
                "4/1/C/1-7" => "Infantry",
                "HQ/2/C/1-7" => "Infantry",
                "1/2/C/1-7" => "Infantry",
                "2/2/C/1-7" => "Infantry",
                "3/2/C/1-7" => "Infantry",
                "4/2/C/1-7" => "Infantry",
                "HQ/3/C/1-7" => "Infantry",
                "1/3/C/1-7" => "Infantry",
                "2/3/C/1-7" => "Infantry",
                "3/3/C/1-7" => "Infantry",
                "4/3/C/1-7" => "Infantry",
                "HQ/4/C/1-7" => "Infantry",
                "1/4/C/1-7" => "Infantry",
                "2/4/C/1-7" => "Infantry",
                "3/4/C/1-7" => "Infantry",
                "4/4/C/1-7" => "Infantry",
                "HQ/HQ/HQ/1-7" => "Infantry",
                "HQ/HQ/A/2-7" => "Infantry",
                "HQ/1/A/2-7" => "Infantry",
                "1/1/A/2-7" => "Infantry",
                "2/1/A/2-7" => "Infantry",
                "3/1/A/2-7" => "Infantry",
                "4/1/A/2-7" => "Infantry",
                "5/1/A/2-7" => "Infantry",
                "6/1/A/2-7" => "Infantry",
                "HQ/2/A/2-7" => "Infantry",
                "1/2/A/2-7" => "Infantry",
                "2/2/A/2-7" => "Infantry",
                "3/2/A/2-7" => "Infantry",
                "4/2/A/2-7" => "Infantry",
                "5/2/A/2-7" => "Infantry",
                "6/2/A/2-7" => "Infantry",
                "HQ/3/A/2-7" => "Infantry",
                "1/3/A/2-7" => "Infantry",
                "2/3/A/2-7" => "Infantry",
                "3/3/A/2-7" => "Infantry",
                "4/3/A/2-7" => "Infantry",
                "5/3/A/2-7" => "Infantry",
                "6/3/A/2-7" => "Infantry",
                "HQ/HQ/B/2-7" => "Infantry",
                "HQ/1/B/2-7" => "Infantry",
                "1/1/B/2-7" => "Infantry",
                "2/1/B/2-7" => "Infantry",
                "3/1/B/2-7" => "Infantry",
                "4/1/B/2-7" => "Infantry",
                "HQ/2/B/2-7" => "Infantry",
                "1/2/B/2-7" => "Infantry",
                "2/2/B/2-7" => "Infantry",
                "3/2/B/2-7" => "Infantry",
                "4/2/B/2-7" => "Infantry",
                "HQ/3/B/2-7" => "Infantry",
                "1/3/B/2-7" => "Infantry",
                "2/3/B/2-7" => "Infantry",
                "3/3/B/2-7" => "Infantry",
                "4/3/B/2-7" => "Infantry",
                "HQ/HQ/C/2-7" => "Armor",
                "HQ/1/C/2-7" => "Armor",
                "1/1/C/2-7" => "Armor",
                "2/1/C/2-7" => "Armor",
                "3/1/C/2-7" => "Armor",
                "4/1/C/2-7" => "Armor",
                "HQ/2/C/2-7" => "Armor",
                "1/2/C/2-7" => "Armor",
                "2/2/C/2-7" => "Armor",
                "3/2/C/2-7" => "Armor",
                "4/2/C/2-7" => "Armor",
                "HQ/3/C/2-7" => "Armor",
                "1/3/C/2-7" => "Armor",
                "2/3/C/2-7" => "Armor",
                "3/3/C/2-7" => "Armor",
                "4/3/C/2-7" => "Armor",
                "HQ/HQ/E/2-7" => "Infantry",
                "HQ/1/E/2-7" => "Infantry",
                "1/1/E/2-7" => "Infantry",
                "2/1/E/2-7" => "Infantry",
                "3/1/E/2-7" => "Infantry",
                "4/1/E/2-7" => "Infantry",
                "5/1/E/2-7" => "Infantry",
                "6/1/E/2-7" => "Infantry",
                "HQ/2/E/2-7" => "Infantry",
                "1/2/E/2-7" => "Infantry",
                "2/2/E/2-7" => "Infantry",
                "3/2/E/2-7" => "Infantry",
                "4/2/E/2-7" => "Infantry",
                "HQ/3/E/2-7" => "Infantry",
                "1/3/E/2-7" => "Infantry",
                "2/3/E/2-7" => "Infantry",
                "3/3/E/2-7" => "Infantry",
                "4/3/E/2-7" => "Infantry",
                "HQ/HQ/HQ/2-7" => "Infantry",
                "HQ/HQ/A/3-7" => "Aviation",
                "HQ/1/A/3-7" => "Aviation",
                "1/1/A/3-7" => "Aviation",
                "2/1/A/3-7" => "Aviation",
                "3/1/A/3-7" => "Aviation",
                "4/1/A/3-7" => "Aviation",
                "HQ/2/A/3-7" => "Aviation",
                "1/2/A/3-7" => "Aviation",
                "2/2/A/3-7" => "Aviation",
                "3/2/A/3-7" => "Aviation",
                "4/2/A/3-7" => "Aviation",
                "HQ/3/A/3-7" => "Aviation",
                "1/3/A/3-7" => "Aviation",
                "2/3/A/3-7" => "Aviation",
                "3/3/A/3-7" => "Aviation",
                "4/3/A/3-7" => "Aviation",
                "HQ/HQ/B/3-7" => "Aviation",
                "HQ/1/B/3-7" => "Aviation",
                "1/1/B/3-7" => "Aviation",
                "2/1/B/3-7" => "Aviation",
                "3/1/B/3-7" => "Aviation",
                "4/1/B/3-7" => "Aviation",
                "HQ/2/B/3-7" => "Aviation",
                "1/2/B/3-7" => "Aviation",
                "2/2/B/3-7" => "Aviation",
                "3/2/B/3-7" => "Aviation",
                "4/2/B/3-7" => "Aviation",
                "HQ/3/B/3-7" => "Aviation",
                "1/3/B/3-7" => "Aviation",
                "2/3/B/3-7" => "Aviation",
                "3/3/B/3-7" => "Aviation",
                "4/3/B/3-7" => "Aviation",

                "HQ/HQ/C/3-7" => "Aviation",
                "HQ/1/C/3-7" => "Aviation",
                "1/1/C/3-7" => "Aviation",
                "2/1/C/3-7" => "Aviation",
                "3/1/C/3-7" => "Aviation",
                "4/1/C/3-7" => "Aviation",
                "HQ/2/C/3-7" => "Aviation",
                "1/2/C/3-7" => "Aviation",
                "2/2/C/3-7" => "Aviation",
                "3/2/C/3-7" => "Aviation",
                "4/2/C/3-7" => "Aviation",
                "HQ/3/C/3-7" => "Aviation",
                "1/3/C/3-7" => "Aviation",
                "2/3/C/3-7" => "Aviation",
                "3/3/C/3-7" => "Aviation",
                "4/3/C/3-7" => "Aviation",

                "HQ/HQ/HQ/3-7" => "Aviation",
                "HQ/HQ/A/ACD" => "Aviation",
                "HQ/1/A/ACD" => "Aviation",
                "HQ/2/A/ACD" => "Aviation",
                "HQ/3/A/ACD" => "Aviation",
                "1/1/A/ACD" => "Aviation",
                "2/1/A/ACD" => "Aviation",
                "3/1/A/ACD" => "Aviation",
                "4/1/A/ACD" => "Aviation",
                "5/1/A/ACD" => "Aviation",
                "1/2/A/ACD" => "Aviation",
                "2/2/A/ACD" => "Aviation",
                "3/2/A/ACD" => "Aviation",
                "4/2/A/ACD" => "Aviation",
                "5/2/A/ACD" => "Aviation",
                "1/3/A/ACD" => "Aviation",
                "2/3/A/ACD" => "Aviation",
                "3/3/A/ACD" => "Aviation",
                "4/3/A/ACD" => "Aviation",
                "5/3/A/ACD" => "Aviation",
                "HQ/HQ/B/ACD" => "Infantry",
                "HQ/1/B/ACD" => "Infantry",
                "1/1/B/ACD" => "Infantry",
                "2/1/B/ACD" => "Infantry",
                "3/1/B/ACD" => "Infantry",
                "4/1/B/ACD" => "Infantry",
                "5/1/B/ACD" => "Infantry",
                "HQ/2/B/ACD" => "Infantry",
                "1/2/B/ACD" => "Infantry",
                "2/2/B/ACD" => "Armor",
                "3/2/B/ACD" => "Infantry",
                "4/2/B/ACD" => "Infantry",
                "5/2/B/ACD" => "Infantry",
                "HQ/HQ/C/ACD" => "Infantry",
                "HQ/1/C/ACD" => "Infantry",
                "1/1/C/ACD" => "Infantry",
                "2/1/C/ACD" => "Aviation",
                "3/1/C/ACD" => "Aviation",
                "4/1/C/ACD" => "Infantry",
                "HQ/2/C/ACD" => "Infantry",
                "1/2/C/ACD" => "Infantry",
                "2/2/C/ACD" => "Infantry",
                "3/2/C/ACD" => "Infantry",
                "4/2/C/ACD" => "Infantry",
                "HQ/HQ/HQ/ACD" => "Infantry",


                "HQ/HQ/D/ACD" => "Infantry",
                "HQ/1/D/ACD" => "Infantry",
                "1/1/D/ACD" => "Infantry",
                "HQ/2/D/ACD" => "Infantry",
                "1/2/D/ACD" => "Infantry",
                "2/1/D/ACD" => "Infantry",
                "3/1/D/ACD" => "Infantry",
                "4/1/D/ACD" => "Infantry",
                "2/2/D/ACD" => "Infantry",
                "3/2/D/ACD" => "Infantry",
                "4/2/D/ACD" => "Infantry",

                "1/Star Citizen/SP/ACD" => "Aviation",
                "1/SWRPG/SP/ACD" => "Infantry",
                "1/CS2/SP/ACD" => "Infantry",
            });
            if (!$row["serviceBranche"]) {
                $row["serviceBranche"] = trim(match (implode("/", [$row["company"], $row["battalion"]])) {
                    default => "",
                    "Support/Regimental HQ" => "Support",
                    "Command Staff/Regimental HQ" => "Command",
                    "Reserve/ACD" => "Reserve",
                    "RTC/Regimental HQ" => "Infantry",
                });
            }
            if (!$row["serviceBranche"]) {
                echo $fullSection;
                echo "\n";
            }
            foreach ($row as $k => $val) {
                $row[$k] = trim($val);
            }

            $serviceBranch = $serviceBranchRepository->findOneBy(["title" => $row["serviceBranche"]]) ?: new ServiceBranch();
            $serviceBranch->setTitle($row["serviceBranche"]);
            $this->entityManager->persist($serviceBranch);
            $this->entityManager->flush();

            $battalion = $battalionRepository->findOneBy(["title" => $row["battalion"]]) ?: new Battalion();
            $battalion->setTitle($row["battalion"]);
            // $battalion->setCustomName($row["battalion"]);
            $this->entityManager->persist($battalion);
            $this->entityManager->flush();

            $company = $companyRepository->findOneBy(["title" => $row["company"], "battalion" => $battalion->getId()]) ?: new Company();
            $company->setTitle($row["company"]);
            // $company->setCustomName($row["company"]);
            $company->setBattalion($battalion);
            $this->entityManager->persist($company);
            $this->entityManager->flush();

            $platoon = $platoonRepository->findOneBy(["title" => $row["platoon"], "company" => $company->getId()]) ?: new Platoon();
            $platoon->setTitle($row["platoon"]);
            // $platoon->setCustomName($row["platoon"]);
            $platoon->setCompany($company);
            $this->entityManager->persist($platoon);
            $this->entityManager->flush();

            $section = $sectionRepository->findOneBy(["title" => $row["section"], "platoon" => $platoon->getId()]) ?: new Section();
            $section->setTitle($row["section"]);
            // $section->setCustomName($row["section"]);
            $section->setPlatoon($platoon);
            $section->setServiceBranch($serviceBranch);
            $this->entityManager->persist($section);
            $this->entityManager->flush();

            $billetAssignment = $billetAssignmentRepository->findOneBy(["milpacId" => trim($row["milpacId"])]) ?: new BilletAssignment();
            $billetAssignment->setMilpacId(trim($row["milpacId"]));
            $billetAssignment->setMilpacTitle(trim($row["milpacTitle"]));
            $billetAssignment->setPositionTitle(trim($row["position"]));
            $billetAssignment->setSection($section);
            $this->entityManager->persist($billetAssignment);
            $this->entityManager->flush();
        }
    }

    private function parseBilletString($str)
    {
        $primary = $str;
        $primary = str_replace("Reserve Commander", "Reserve Commanding Officer", $primary);
        $primary = str_replace("Auxiliary Commander", "ACD Commanding Officer", $primary);
        $primary = str_replace("Auxiliary", "ACD", $primary);
        if (str_starts_with($primary, 'Reserve ')) {
            return ["battalion" => "ACD", "company" => "Reserve", "platoon" => "Reserve", "section" => "Reserve", "assignment" => str_replace('Reserve ', "", $primary)];
        }
        if ($primary === "New Recruit") {
            return ["battalion" => "Regimental", "company" => "RTC", "platoon" => "RTC", "section" => "RTC", "assignment" => "New Recruit"];
        }
        if (preg_match("@^(Section Leader |Trooper |Assistant Section Leader |)(.*?)\sStart-Up$@", $primary, $matches)) {
            return [
                "battalion" => "ACD",
                "company" => "SP",
                "platoon" => "{$matches[2]}",
                "section" => "1",
                "assignment" => trim($matches[1]) ?: "Trooper",
            ];
        }
        // $patternBattalion = "\d-\d|ACD|Regimental|Reserve";
        $patternBattalion = "\d-\d|ACD|Regimental";
        $parts = [$patternBattalion, "\w", "\d", "\d"];
        for ($length = count($parts); $length > 0; $length--) {
            $pattern = [];
            for ($j = $length - 1; $j >= 0; $j--) {
                $pattern [] = "({$parts[$j]})";
            }
            $pattern = implode('/', $pattern);
            if (preg_match("@^(.*)\s$pattern$@", $primary, $matches)) {
                array_shift($matches);
                $assignment = array_shift($matches);
                $matches = array_reverse($matches);
                $keys = [
                    "battalion",
                    "company",
                    "platoon",
                    "section",
                    // "assignment",
                ];
                $data = [];
                foreach ($keys as $i => $key) {
                    $data[$key] = $matches[$i] ?? "HQ";
                }
                $data["assignment"] = $assignment;
                return $data;
            }
        }

        if (preg_match("@^($patternBattalion)\s(Adjutant General|Technical Aide|Commanding Officer|Executive Officer|Sergeant Major|Command Sergeant Major|Information Management Officer|Chief of Staff|Recruiting Oversight Officer|Security Operations Officer|Support Staff)$@", $primary, $matches)) {
            return [
                "battalion" => "{$matches[1]}",
                "company" => $matches[1] === "Regimental" ? "Command Staff" : "HQ",
                "platoon" => $matches[1] === "Regimental" ? "Command Staff" : "HQ",
                "section" => $matches[1] === "Regimental" ? "Command Staff" : "HQ",
                "assignment" => $matches[2],
            ];
        }
        if (preg_match("@Aide to the (.*)@", $primary, $matches)) {
            return [
                "battalion" => "Regimental",
                "company" => "Command Staff",
                "platoon" => "Command Staff",
                "section" => "Aide",
                "assignment" => $primary,
            ];
        }
        if (in_array($primary, ["Information Management Officer", ""])) {
            return [
                "battalion" => "Regimental",
                "company" => "Command Staff",
                "platoon" => "Command Staff",
                "section" => "Aide",
                "assignment" => $primary,
            ];
        }
        if ($primary === "Officer Pool") {
            return [
                "battalion" => "Regimental",
                "company" => "Command Staff",
                "platoon" => "Command Staff",
                "section" => "Officer Pool",
                "assignment" => "Officer",
            ];
        }
        if ($primary === "FCC Analyst") {
            return [
                "battalion" => "ACD",
                "company" => "HQ",
                "platoon" => "HQ",
                "section" => "Future Concepts Center",
                "assignment" => "Analyst",
            ];
        }
        // $primary = preg_replace("@S1 (Uniforms|Milpacs|Operations|Tracker|Citations) IT@", "S1 $1 Clerk IT", $primary);

        //|IT|Lead Auditor|Senior Auditor|Auditor|Auditor IT|Senior Development Staff|Development Staff|Development Staff IT
        $primary = preg_replace("@^(S\d) ARMA (.*)$@", "$1 Arma $2", $primary);
        $primary = match ($primary) {
            "S5 Graphics" => "S5 Graphics Clerk",
            "S5 Filmmaker" => "S5 Filmmaker Clerk",
            "WAG Lead Auditor" => "WAG Auditor Lead",
            "WAG Senior Auditor" => "WAG Auditor Senior",
            "WAG Auditor" => "WAG Auditor Clerk",
            "S6 Senior Development Staff" => "S6 Development Senior",
            "S6 Development Staff IT" => "S6 Development IT",
            "S6 Senior Forums Staff" => "S6 Forums Senior",
            "S6 Senior Game Staff" => "S6 Game Senior",
            "S6 Senior Operations Staff" => "S6 Operations Senior",
            "S5 Public Relations" => "S5 Public Relations Clerk",
            "S5 Lead Filmmaker" => "S5 Filmmaker Lead",
            "S5 Senior Filmmaker" => "S5 Filmmaker Senior",
            "RTC Lead Drill Instructor" => "RTC Drill Instructor Lead Instructor",
            "RTC Senior Drill Instructor" => "RTC Drill Instructor Senior Instructor",
            "RTC Drill Instructor" => "RTC Drill Instructor Instructor",
            "RTC Drill Instructor IT" => "RTC Drill Instructor Instructor Trainee",
            "S2 Lead Investigator" => "S2 Investigator Lead Investigator",
            "S2 Senior Investigator" => "S2 Investigator Senior Investigator",
            "S2 Investigator" => "S2 Investigator Investigator",
            "S2 Investigator IT" => "S2 Investigator Investigator Trainee",

            "RRD Senior Recruiter" => "RRD Recruiter Senior Recruiter",
            "RRD Recruiter" => "RRD Recruiter Recruiter",
            "RRD Lead Processing Clerk" => "RRD Processing Lead",
            "RRD Processing Clerk" => "RRD Processing Clerk",
            "RRD AO Lead - Arma 3" => "RRD Arma Lead",
            "RRD Recruiter - Arma 3" => "RRD Arma Recruiter",
            "RRD AO Lead - Hell Let Loose" => "RRD HLL Lead",
            "RRD AO Lead - ACD" => "RRD ACD Lead",
            "RRD Recruiter - Hell Let Loose" => "RRD HLL Recruiter",
            "RRD Processing Clerk IT" => "RRD Processing IT",
            "RRD Recruiter - DCS" => "RRD DCS Recruiter",
            "RRD Recruiter - ACD" => "RRD ACD Recruiter",
            "S3 Arma Scripting" => "S3 Arma Scripting Clerk",
            "S3 Arma Mission Designer" => "S3 Arma Mission Designer Clerk",
            "S3 Arma Senior Mission Designer" => "S3 Arma Mission Designer Senior",
            "S3 Arma Lead Mission Designer" => "S3 Arma Mission Designer Lead",


            "S3 Arma Lead Public Staff" => "S3 Arma Public Staff Lead",
            "S3 Arma Senior Public Staff" => "S3 Arma Public Staff Senior",
            "S3 Arma Public Staff" => "S3 Arma Public Staff Clerk",

            "S3 HLL Lead Public Staff" => "S3 HLL Public Staff Lead",
            "S3 HLL Senior Public Staff" => "S3 HLL Public Staff Senior",
            "S3 HLL Public Staff" => "S3 HLL Public Staff Clerk",
            // "S3 HLL Console Staff IT" => "S3 HLL Console Staff Trainee",
            "S3 HLL Console Staff IT" => "S3 HLL Console Operations Trainee",
            "S3 Arma Public Staff IT" => "S3 Arma Public Staff Trainee",
            "S3 HLL Public Staff IT" => "S3 HLL Public Staff Trainee",
            "S7 Publisher" => "S7 Publisher Clerk",

            "MP Lead Administrator" => "MP Administrator Lead",
            "MP Senior Administrator" => "MP Administrator Senior",
            "MP Administrator" => "MP Administrator Clerk",
            "MP Administrator IT" => "MP Administrator IT",

            "ODS Lead Instructor" => "S7 ODS Lead Instructor",
            "ODS Instructor" => "S7 ODS Instructor",
            "ODS Instructor IT" => "S7 ODS Instructor IT",
            "NCOA Lead Instructor" => "S7 NCOA Lead Instructor",
            "NCOA Instructor" => "S7 NCOA Instructor",
            "NCOA Instructor IT" => "S7 NCOA Instructor IT",
            "NCOA Senior Instructor" => "S7 NCOA Senior Instructor",
            "SPD Lead" => "S7 SPD Lead",
            "SPD Coordinator" => "S7 SPD Coordinator",

            default => $primary,
        };
        if (preg_match("@^(S\d|WAG|RRD|RTC|MP|JAG)\s(.+)$@", $primary, $matches)) {
            $platoon = $matches[1];
            $positionAndSection = trim($matches[2]);
            $positionAndSection = preg_replace("@Staff$@", "Clerk", $positionAndSection);
            $positionAndSection = preg_replace("@Clerk IT$@", "Trainee", $positionAndSection);
            $positionAndSection = preg_replace("@Staff IT$@", "Trainee", $positionAndSection);
            $positionAndSection = preg_replace("@IT$@", "Trainee", $positionAndSection);
            $sectionPatter = implode("|", match ($platoon) {
                "S1" => [
                    'Milpacs',
                    'Uniforms',
                    'Operations',
                    'Citations',
                    'Tracker',
                    'Technical',
                    'Analytics',
                ],
                "WAG" => [
                    "Admin",
                    "Auditor",
                ],
                "S6" => [
                    "DevOps",
                    "Development",
                    "Game/Forum",
                    "Game",
                    "Forums",
                    "Operations",
                ],
                "S5" => [
                    "Public Relations",
                    "Graphics",
                    "Filmmaker",
                    "Social Media",
                ],
                "RTC" => [
                    "Drill Instructor"
                ],
                "S2" => [
                    "Investigator"
                ],
                "RRD" => [
                    'DCS',
                    'HLL',
                    'Arma',
                    'Recruiter',
                    'Processing',
                    'ACD',
                ],
                "S3" => [
                    "Arma Operations",
                    "Arma Mission Designer",
                    "Arma Scripting",
                    "HLL Operations",
                    "HLL Console Operations",
                    "Squad",
                    "RoN",
                    "DCS",
                    "Arma Public Staff",
                    "HLL Public Staff",
                    // "Arma Mission Designer",
                ],
                "S7" => [
                    "Arma AGCS",
                    "Arma IS",
                    "Arma TAS",
                    "Arma TCS",
                    "Squad CAS",
                    "HLL SOI",
                    "HLL THQ",
                    "DCS ITW",
                    "DCS FTW",
                    "DCS STW",
                    "HLL CDS",
                    "RoN CQBA",
                    "HLL SOA",
                    "DCS RTW",
                    "1BN",
                    "2BN",
                    "Publisher",
                    "ODS",
                    "NCOA",
                    "SPD",
                ],
                "MP" => [
                    "Administrator",
                ],
                "JAG" => [],
            });
            $positionPattern = implode("|", match ($platoon) {
                "S7" => ["Instructor", "Instructor Trainee", "Lead Instructor", "Senior Instructor", "Lead", "Clerk", "Coordinator"],
                "RRD" => ["Senior Recruiter", "Recruiter", "Lead", "Senior", "Clerk", "Trainee"],
                "RTC" => ["Instructor", "Instructor Trainee", "Lead Instructor", "Senior Instructor"],
                "S2" => ["Investigator", "Investigator Trainee", "Lead Investigator", "Senior Investigator"],
                default => ["Lead", "Senior", "Clerk", "Trainee"],
            });
            if ($sectionPatter && preg_match("@^($sectionPatter)\s($positionPattern)$@", $positionAndSection, $matches)) {
                return [
                    "battalion" => "Regimental",
                    "company" => "Support",
                    "platoon" => "{$platoon}",
                    "section" => "{$matches[1]}",
                    "assignment" => $matches[2],
                ];
            } elseif (in_array($positionAndSection, ["1IC", "2IC", "Test Account", "Assistant", "Project Coordinator", "Specialist"])) {
                return [
                    "battalion" => "Regimental",
                    "company" => "Support",
                    "platoon" => "{$platoon}",
                    "section" => "HQ",
                    "assignment" => $positionAndSection,
                ];
            } else {
                throw new \Exception("unhandled billet assignment for platoon '$platoon': '$primary'");
            }
        }
        throw new \Exception("unhandled billet assignment '$primary'");
    }
}
