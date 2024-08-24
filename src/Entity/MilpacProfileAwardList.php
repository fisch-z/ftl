<?php

declare(strict_types=1);

namespace App\Entity;

use Traversable;

class MilpacProfileAwardList implements \IteratorAggregate
{
    private array $awards = [];
    private int $yearsInServiceStripesCount;

    public function __construct(array $_awardsOnProfile, private readonly MilpacProfile $milpacProfile, private readonly array $supportDepartments)
    {
        $awardGroupingMap = [];
        $toReplace = [
            "Expert Infantry Badge" => "Infantry Badge",
            "Combat Infantry Badge" => "Infantry Badge",
            "Combat Infantry Badge 2nd Award" => "Infantry Badge",
            "Combat Infantry Badge 3rd Award" => "Infantry Badge",
            "Combat Infantry Badge 4th Award" => "Infantry Badge",

            "Army Aviator Badge" => "Aviation Badge",
            "Senior Army Aviator Badge" => "Aviation Badge",
            "Master Army Aviator Badge" => "Aviation Badge",

            "Aircraft Crewman Badge" => "Aircraft Crewman Badge",
            "Aircraft Senior Crewman Badge" => "Aircraft Crewman Badge",
            "Aircraft Master Crewman Badge" => "Aircraft Crewman Badge",

            "Rifle Marksman" => "Marksman",
            "Rifle Sharpshooter" => "Sharpshooter",
            "Rifle Expert" => "Expert",
            "Grenade Marksman" => "Marksman",
            "Grenade Sharpshooter" => "Sharpshooter",
            "Grenade Expert" => "Expert",
            "M-203 Marksman" => "Marksman",
            "M-203 Sharpshooter" => "Sharpshooter",
            "M-203 Expert" => "Expert",
            "Machine Gun Marksman" => "Marksman",
            "Machine Gun Sharpshooter" => "Sharpshooter",
            "Machine Gun Expert" => "Expert",
            "Recoilless Rifle Marksman" => "Marksman",
            "Recoilless Rifle Sharpshooter" => "Sharpshooter",
            "Recoilless Rifle Expert" => "Expert",
            "Pistol Marksman" => "Marksman",
            "Pistol Sharpshooter" => "Sharpshooter",
            "Pistol Expert" => "Expert",
            "Aeroweapons Marksman" => "Marksman",
            "Aeroweapons Sharpshooter" => "Sharpshooter",
            "Aeroweapons Expert" => "Expert",
            "Tank Weapons Expert" => "Expert",
            "Tank Weapons Marksman" => "Marksman",
            "Tank Weapons Sharpshooter" => "Sharpshooter",
            "Hydra-70 Marksman" => "Marksman",
            "Hydra-70 Sharpshooter" => "Sharpshooter",
            "Hydra-70 Expert" => "Expert",

            "Master Instructor Badge" => "Instructor Badge",
            "Senior Instructor Badge" => "Instructor Badge",
            "Master Recruiter Badge" => "Recruiter Badge",
            "Gold Recruiter Badge" => "Recruiter Badge",
            "Senior Recruiter Badge" => "Recruiter Badge",

            "Senior Mission Controller Badge" => "Mission Controller Badge",
            "Master Mission Controller Badge" => "Mission Controller Badge",

            // "Combat Field Medical Badge" => "Field Medical Badge",
            // "Expert Field Medical Badge" => "Field Medical Badge",

            // "Basic Explosive Ordnance Disposal Badge" => "Explosive Ordnance Disposal Badge",
            // "Senior Explosive Ordnance Disposal Badge" => "Explosive Ordnance Disposal Badge",
            // "Master Explosive Ordinance Disposal Badge" => "Explosive Ordinance Disposal Badge",

            // "Forward Air Controller Badge" => "Forward Air Controller Badge",
            // "Senior Forward Air Controller Badge" => "Forward Air Controller Badge",
            // "Master Forward Air Controller Badge" => "Forward Air Controller Badge",

            // "High Altitude Low Opening (HALO) Freefall Badge" => "High Altitude Low Opening Badge",
            // "Master High Altitude Low Opening (HALO) Freefall Badge" => "High Altitude Low Opening Badge",

            // "Army Parachutist Badge" => "Army Parachutist Badge",
            // "Army Senior Parachutist Badge" => "Army Parachutist Badge",
            // "Army Master Parachutist Badge" => "Army Parachutist Badge",

            // "Flight Medic Badge" => "Medical Badge",
        ];
        // foreach(array_keys(static::$awardTypes["weaponCertifications"]) as $name) {
        //     $toReplace[$name] = preg_replace("@^.*(Marksman|Sharpshooter|Expert)$@", "$1", $name);
        // }
        foreach ($toReplace as $name => $groupingName) {
            $awardGroupingMap[$name] = $groupingName;
        }
        $awardsOnProfile = [];
        foreach ($_awardsOnProfile as $awardOnProfile) {
            $withValour = str_contains($awardOnProfile["awardName"], "with Valor Device");
            $name = str_replace(" with Valor Device", "", $awardOnProfile["awardName"]);
            $name = str_replace('"', '', $name);
            $name = str_replace('/', ' ', $name);
            $newName = $awardGroupingMap[$name] ?? $name;
            $awardsOnProfile[$newName] = $awardsOnProfile[$newName] ?? ["rows" => []];
            $awardsOnProfile[$newName]["rows"][] = [
                "withValor" => $withValour,
                "details" => $awardOnProfile["awardDetails"],
                "date" => $awardOnProfile["awardDate"],
                "originalAwardName" => $name,
            ];
        }
        $this->yearsInServiceStripesCount = count($awardsOnProfile["Army Good Conduct Medal"]["rows"] ?? []);
        foreach (self::$awardTypes as $category => $types) {
            $this->awards[$category] = [];
            foreach (array_reverse($types) as $name => $typeMeta) {
                if (array_key_exists($name, $awardsOnProfile)) {
                    // $groupTypeName = $typeMeta["stackingGroupName"] ?? $name;
                    unset($typeMeta["stackingGroupName"]);
                    $awardGroupName = $typeMeta["awardGroup"] ?? $name;
                    $extraRows = [];
                    if ($awardGroupName === "High Altitude Low Opening Badge") {
                        $extraRows = array_values(array_filter(array_map(fn($record) => $record["recordType"] === "RECORD_TYPE_OPERATION" && preg_match("@(Completed (\d+)(st|nd|rd|th) HALO Combat Jump)@", $record["recordDetails"], $matches) ? ["details" => $matches[1], "date" => $record["recordDate"], "withValor" => false, "originalAwardName" => $awardGroupName] : null, $this->milpacProfile->getData()["records"])));
                    }
                    if ($awardGroupName === "Army Parachutist Badge") {
                        $extraRows = array_values(array_filter(array_map(fn($record) => $record["recordType"] === "RECORD_TYPE_OPERATION" && preg_match("@(Completed (\d+)(st|nd|rd|th) Combat Jump)@", $record["recordDetails"], $matches) ? ["details" => $matches[1], "date" => $record["recordDate"], "withValor" => false, "originalAwardName" => $awardGroupName] : null, $this->milpacProfile->getData()["records"])));
                    }
                    $this->awards[$category][$awardGroupName] = new MilpacProfileAward(
                        $category,
                        $name,
                        $typeMeta,
                        array_merge($awardsOnProfile[$name]["rows"], isset($this->awards[$category][$awardGroupName]) ? $this->awards[$category][$awardGroupName]->getRows() : []),
                        $extraRows,
                    );
                }
            }
        }
    }

    public function getYearsInServiceStripesCount(): int
    {
        return min(10, $this->yearsInServiceStripesCount);
    }

    public function getIterator(): Traversable
    {
        yield from [
            "Medals" => $this->getMedals(),
            "Ribbons" => $this->getRibbons(),
            "Tabs" => $this->getTabs(),
            "UnitCitations" => $this->getUnitCitations(),
            "primarySpecialSkills" => $this->getPrimarySpecialSkills(),
            "secondarySpecialSkills" => $this->getSecondarySpecialSkills(),
            "WeaponCertifications" => $this->getWeaponCertifications(),
            "awardedBadges" => $this->getAwardedBadges(),
            "awardedBadgesStackable" => $this->getAwardedBadgesStackable(),
        ];
    }


    /**
     * @return array|MilpacProfileAward[]
     */
    public function getMedals(): array
    {
        return array_values($this->awards["medals"]);
    }

    /**
     * @return array|MilpacProfileAward[]
     */
    public function getRibbons(): array
    {
        return array_values($this->awards["ribbons"]);
    }

    /**
     * @return array|MilpacProfileAward[]
     */
    public function getTabs(): array
    {
        return array_values($this->awards["tabs"]);
    }

    /**
     * @return array|MilpacProfileAward[]
     */
    public function getUnitCitations(): array
    {
        return array_values($this->awards["unitCitations"]);
    }

    /**
     * @return array|MilpacProfileAward[]
     */
    public function getPrimarySpecialSkills(): array
    {
        $awards = array_values($this->awards["primarySpecialSkills"]);
        $override = $this->milpacProfile->getMilpacProfileUniformOverride();
        $serviceBranch = $override && $override->getPreferredPrimarySpecialSkillServiceBranch() ? $override->getPreferredPrimarySpecialSkillServiceBranch() : $this->milpacProfile->getServiceBranch();
        usort($awards, function (MilpacProfileAward $a, MilpacProfileAward $b) use ($serviceBranch) {
            if (strtolower($a->getName()) === strtolower("{$serviceBranch->getTitle()} Badge")) {
                return 1;
            }
            if (strtolower($b->getName()) === strtolower("{$serviceBranch->getTitle()} Badge")) {
                return -1;
            }
            if ($serviceBranch->getTitle() === "Medical") {
                if ($a->getName() === "Flight Medic Badge") {
                    return 1;
                }
                if ($b->getName() === "Flight Medic Badge") {
                    return -1;
                }
            }
            if ($serviceBranch->getTitle() === "Aviation") {
                if ($a->getName() === "Aircraft Crewman Badge") {
                    return 1;
                }
                if ($b->getName() === "Aircraft Crewman Badge") {
                    return -1;
                }
            }
            return $a->getStackingCount() <=> $b->getStackingCount();
        });
        return array_reverse($awards);
    }

    /**
     * @return array|MilpacProfileAward[]
     */
    public function getSecondarySpecialSkills(): array
    {
        $awards = array_values($this->awards["secondarySpecialSkills"]);
        $override = $this->milpacProfile->getMilpacProfileUniformOverride();
        usort($awards, function (MilpacProfileAward $a, MilpacProfileAward $b) use ($override) {
            if ($override) {
                if ($a->getName() === $override->getPreferredSecondarySpecialSkill1()) {
                    return 1;
                }
                if ($b->getName() === $override->getPreferredSecondarySpecialSkill1()) {
                    return -1;
                }
                if ($a->getName() === $override->getPreferredSecondarySpecialSkill2()) {
                    return 1;
                }
                if ($b->getName() === $override->getPreferredSecondarySpecialSkill2()) {
                    return -1;
                }
                if ($a->getName() === $override->getPreferredSecondarySpecialSkill3()) {
                    return 1;
                }
                if ($b->getName() === $override->getPreferredSecondarySpecialSkill3()) {
                    return -1;
                }
            }
            return $a->getStackingCount() <=> $b->getStackingCount();
        });
        return array_reverse($awards);
    }

    /**
     * @return array|MilpacProfileAward[]
     */
    public function getWeaponCertifications(): array
    {
        return array_values($this->awards["weaponCertifications"]);
    }


    /**
     * @return array|MilpacProfileAward[]
     */
    public function getAwardedBadges(): array
    {
        return array_values($this->awards["awardedBadges"]);
    }


    /**
     * @return array|MilpacProfileAward[]
     */
    public function getAwardedBadgesStackable(): array
    {
        return array_values($this->awards["awardedBadgesStackable"]);
    }

    public function getArmedForcesServiceMedal(): ?MilpacProfileAward
    {
        foreach ($this->getMedals() as $medal) {
            if ($medal->getName() === "Armed Forces Service Medal") {
                return $medal;
            }
        }
        return null;
    }

    private static $awardTypes = [
        "medals" => [
            // "James Krazee Foster Lifetime Achievement Medal" => ["stackingType" => ""],
            // "Ronnie Coldblud Bussey Lifetime Achievement Medal" => ["stackingType" => ""],
            // "Army Distinguished Service Cross" => ["stackingType" => "Leaf"],
            // "Defense Distinguished Service Medal" => ["stackingType" => "Leaf"],
            // "Army Distinguished Service Medal" => ["stackingType" => "Leaf"],//XXX
            // "Defense Superior Service Medal" => ["stackingType" => "Leaf"],
            // "Soldiers Medal" => ["stackingType" => "Leaf"],
            // "Defense Meritorious Service Medal" => ["stackingType" => "Leaf"],
            // "Meritorious Service Medal" => ["stackingType" => "Leaf"],
            // "Army Air Medal" => ["stackingType" => "Numeral"],
            // "Joint Service Commendation Medal" => ["stackingType" => "Leaf"],
            // // "Army Commendation Medal with Valor Device" => "Leaf w/V",
            // // "Army Commendation Medal" => "Leaf w/V",
            // "Army Commendation Medal" => ["stackingType" => "Leaf"],
            // "Joint Service Achievement Medal" => ["stackingType" => "Leaf"],
            // // "Joint Serivce Achievement Medal" => ["stackingType" => "Leaf"],
            // "Army Achievement Medal" => ["stackingType" => "Leaf"],
            // "Prisoner of War Medal" => ["stackingType" => "Leaf"],
            // "Army Good Conduct Medal" => ["stackingType" => "Bar"],
            // "Womens Army Corp Service Medal" => ["stackingType" => ""],
            // "Armed Forces Expeditionary Medal" => ["stackingType" => "Star"],
            // "Afghanistan Campaign Medal" => ["stackingType" => "Star"],
            // // "Afghanisatn Campaign Medal" => ["stackingType" => "Star"],
            // "Iraq Campaign Medal" => ["stackingType" => "Star"],
            // "Global War on Terrorism Expeditionary Medal" => ["stackingType" => "Star"],
            // "National Defense Service Medal" => ["stackingType" => "Star"],
            // "Armed Forces Service Medal" => ["stackingType" => "Star"],
            // "Humanitarian Service Medal" => ["stackingType" => "Star"],
            // "StackUp Donation Medal" => ["stackingType" => "Knot"],
            // "Outstanding Volunteer Service Medal" => ["stackingType" => "Star"],
            // "Cavalry Centurion Medal" => ["stackingType" => "Star"],
            // "United Nations Service Medal" => ["stackingType" => "Star"],
            // "D-Day Commemorative Medal" => ["stackingType" => ""],
            // "European African Middle Eastern Campaign Medal" => ["stackingType" => ""],
            // "American Defense Medal" => ["stackingType" => ""],
            // "Silver Star" => ["stackingType" => "Leaf"],
            // "Bronze Star" => ["stackingType" => "Leaf"],

            "James Krazee Foster Lifetime Achievement Medal" => ["stackingType" => ""],
            "Ronnie Coldblud Bussey Lifetime Achievement Medal" => ["stackingType" => ""],
            "Army Distinguished Service Cross" => ["stackingType" => "Leaf"],
            "Defense Distinguished Service Medal" => ["stackingType" => "Leaf"],
            "Army Distinguished Service Medal" => ["stackingType" => "Leaf"],
            "Silver Star" => ["stackingType" => "Leaf"],
            "Defense Superior Service Medal" => ["stackingType" => "Leaf"],
            "Legion of Merit" => ["stackingType" => "Leaf"],
            "Distinguished Flying Cross" => ["stackingType" => "Leaf"],
            "Soldiers Medal" => ["stackingType" => "Leaf"],
            "Bronze Star" => ["stackingType" => "Leaf"],
            "Purple Heart" => ["stackingType" => "Leaf"],
            "Defense Meritorious Service Medal" => ["stackingType" => "Leaf"],
            "Meritorious Service Medal" => ["stackingType" => "Leaf"],
            "Army Air Medal" => ["stackingType" => "Numeral"],
            "Joint Service Commendation Medal" => ["stackingType" => "Leaf"],
            "Army Commendation Medal" => ["stackingType" => "Leaf"],
            "Joint Service Achievement Medal" => ["stackingType" => "Leaf"],
            "Army Achievement Medal" => ["stackingType" => "Leaf"],
            "Prisoner of War Medal" => ["stackingType" => "Leaf"],
            "Army Good Conduct Medal" => ["stackingType" => "Knot"],
            "Womens Army Corp Service Medal" => ["stackingType" => ""],
            "Armed Forces Expeditionary Medal" => ["stackingType" => "Star"],
            "Afghanistan Campaign Medal" => ["stackingType" => "Star"],
            "Iraq Campaign Medal" => ["stackingType" => "Star"],
            "Global War on Terrorism Expeditionary Medal" => ["stackingType" => "Star"],
            "National Defense Service Medal" => ["stackingType" => "Star"],
            "Armed Forces Service Medal" => ["stackingType" => "Star"],
            "Humanitarian Service Medal" => ["stackingType" => "Star"],
            "7th Cavalry Server Upgrade Award" => ["stackingType" => "StarSilver"],
            "StackUp Donation Medal" => ["stackingType" => "KnotNoStars"],
            "Outstanding Volunteer Service Medal" => ["stackingType" => "Star"],
            "Cavalry Centurion Medal" => ["stackingType" => "StarSilver"],
            "United Nations Service Medal" => ["stackingType" => "Star"],

            "Overseas Service Ribbon" => ["stackingType" => "LeafAO"],
            "Ready or Not Service Ribbon" => ["stackingType" => "LeafAO"],
            "Squad Service Ribbon" => ["stackingType" => "LeafAO"],
            //"WWII Service Ribbon" => ["stackingType" => "Numeral"],
            "Hell Let Loose Service Ribbon" => ["stackingType" => "LeafAO"],
            "Hell Let Loose Console Service Ribbon" => ["stackingType" => "LeafAO"],
            "DCS World Service Ribbon" => ["stackingType" => "LeafAO"],

            "D-Day Commemorative Medal" => ["stackingType" => ""],
            "Sniper Ribbon" => ["stackingType" => ""],
            "European African Middle Eastern Campaign Medal" => ["stackingType" => ""],
            "American Defense Medal" => ["stackingType" => ""],

        ],
        "ribbons" => [
            "James Krazee Foster Lifetime Achievement Medal" => ["stackingType" => ""],
            "Ronnie Coldblud Bussey Lifetime Achievement Medal" => ["stackingType" => ""],
            "Army Distinguished Service Cross" => ["stackingType" => "Leaf"],
            "Defense Distinguished Service Medal" => ["stackingType" => "Leaf"],
            "Army Distinguished Service Medal" => ["stackingType" => "Leaf"],
            "Silver Star" => ["stackingType" => "Leaf"],
            "Defense Superior Service Medal" => ["stackingType" => "Leaf"],
            "Legion of Merit" => ["stackingType" => "Leaf"],
            "Distinguished Flying Cross" => ["stackingType" => "Leaf"],
            "Soldiers Medal" => ["stackingType" => "Leaf"],
            // "Bronze Star with Valor Device" => "Leaf w/V",
            // "Bronze Star" => "Leaf w/V",
            "Bronze Star" => ["stackingType" => "Leaf"],
            "Purple Heart" => ["stackingType" => "Leaf"],
            "Defense Meritorious Service Medal" => ["stackingType" => "Leaf"],
            "Meritorious Service Medal" => ["stackingType" => "Leaf"],
            "Army Air Medal" => ["stackingType" => "Numeral"],
            "Joint Service Commendation Medal" => ["stackingType" => "Leaf"],
            // "Army Commendation Medal with Valor Device" => "Leaf w/V",
            // "Army Commendation Medal" => "Leaf w/V",
            "Army Commendation Medal" => ["stackingType" => "Leaf"],
            "Joint Service Achievement Medal" => ["stackingType" => "Leaf"],
            // "Joint Serivce Achievement Medal" => ["stackingType" => "Leaf"],
            "Army Achievement Medal" => ["stackingType" => "Leaf"],
            "Prisoner of War Medal" => ["stackingType" => "Leaf"],
            "Army Good Conduct Medal" => ["stackingType" => "Knot"],
            "Womens Army Corp Service Medal" => ["stackingType" => ""],
            "Armed Forces Expeditionary Medal" => ["stackingType" => "Star"],
            "Afghanistan Campaign Medal" => ["stackingType" => "Star"],
            // "Afghanisatn Campaign Medal" => ["stackingType" => "Star"],
            "Iraq Campaign Medal" => ["stackingType" => "Star"],
            "Global War on Terrorism Expeditionary Medal" => ["stackingType" => "Star"],
            "National Defense Service Medal" => ["stackingType" => "Star"],
            "Armed Forces Service Medal" => ["stackingType" => "Star"],
            "Humanitarian Service Medal" => ["stackingType" => "Star"],
            "Donation Ribbon" => ["stackingType" => "Star100"],
            "7th Cavalry Server Upgrade Award" => ["stackingType" => "StarSilver"],
            "StackUp Donation Medal" => ["stackingType" => "KnotNoStars"],
            "Outstanding Volunteer Service Medal" => ["stackingType" => "Star"],
            "NCO Professional Development Ribbon" => ["stackingType" => "Numeral"],
            "Honor Graduate Ribbon" => ["stackingType" => ""],
            "Army Service Ribbon" => ["stackingType" => ""],
            "Cavalry Centurion Medal" => ["stackingType" => "StarSilver"],
            "United Nations Service Medal" => ["stackingType" => "Star"],
            "Overseas Service Ribbon" => ["stackingType" => "LeafAO"],
            "Ready or Not Service Ribbon" => ["stackingType" => "LeafAO"],
            "Squad Service Ribbon" => ["stackingType" => "LeafAO"],
            // "World War II Service Ribbon" => "Numeral",//
            "WWII Service Ribbon" => ["stackingType" => "LeafAO"],
            "Hell Let Loose Service Ribbon" => ["stackingType" => "LeafAO"],
            "Hell Let Loose Console Service Ribbon" => ["stackingType" => "LeafAO"],
            // "DCS Service Ribbon" => ["stackingType" => "Star"],
            "DCS World Service Ribbon" => ["stackingType" => "LeafAO"],
            "D Day Participation Ribbon" => ["stackingType" => ""],
            // "D-Day Participation Ribbon" => ["stackingType" => ""],
            "Recruiting Ribbon" => ["stackingType" => "Star100"],
            // "Recrutiing Service Ribbon" => ["stackingType" => "Star"],
            "D-Day Commemorative Medal" => ["stackingType" => ""],
            "Ranger Selection Ribbon" => ["stackingType" => ""],
            // "Ranger Selection RIbbon" => ["stackingType" => ""],
            "Sniper Ribbon" => ["stackingType" => ""],
            "Basic Assault Course Ribbon" => ["stackingType" => ""],
            "Cadre Course Ribbon" => ["stackingType" => ""],
            "European African Middle Eastern Campaign Medal" => ["stackingType" => ""],
            "American Defense Medal" => ["stackingType" => ""],
            "Reenlistment Ribbon" => ["stackingType" => ""],
        ],
        "tabs" => [
            "Ranger Tab" => ["stackingType" => ""],
            "Sapper Tab" => ["stackingType" => ""],
            "Long-Range Reconnaissance Patrol Tab" => ["stackingType" => ""],
            "Special Forces Tab" => ["stackingType" => ""],
        ],
        "unitCitations" => [
            "7th Cavalry Black Ops Unit Citation" => ["stackingType" => "Leaf"],
            "Army Superior Unit Citation" => ["stackingType" => "Leaf"],
            "Army Meritorious Unit Citation" => ["stackingType" => "Leaf"],
            "Army Valorous Unit Citation" => ["stackingType" => "Leaf"],
            "Joint Meritorious Unit Citation" => ["stackingType" => "Leaf"],
            "Army & Air Force Presidential Unit Citation" => ["stackingType" => "Leaf"],
        ],
        "awardedBadgesStackable" => [
            "Instructor Badge" => ["stackingType" => "", "stackingMaxCount" => 2],
            "Recruiter Badge" => ["stackingType" => "", "stackingMaxCount" => 3],
            "Mission Controller Badge" => ["stackingType" => "", "stackingMaxCount" => 2],
            // "Senior Mission Controller Badge" => ["stackingType" => "", "stackingMaxCount" => 1, "awardGroup" => "Mission Controller Badge"],
            // "Master Mission Controller Badge" => ["stackingType" => "", "stackingMaxCount" => 1, "awardGroup" => "Mission Controller Badge"],
        ],
        "awardedBadges" => [
            // "Master Instructor Badge" => ["stackingType" => ""],
            // "Senior Instructor Badge" => ["stackingType" => ""],
            // "Master Recruiter Badge" => ["stackingType" => ""],
            // "Gold Recruiter Badge" => ["stackingType" => ""],
            // "Senior Recruiter Badge" => ["stackingType" => ""],
            // "Senior Mission Controller Badge" => ["stackingType" => ""],
            // "Master Mission Controller Badge" => ["stackingType" => ""],
            "Best Ranger" => ["stackingType" => ""],
            "Pathfinder Badge" => ["stackingType" => ""],
            "CQB Badge" => ["stackingType" => ""],
            "Cavalry Spurs" => ["stackingType" => ""],
        ],
        "weaponCertifications" => [
            "Marksman" => ["stackingType" => ""],
            "Sharpshooter" => ["stackingType" => ""],
            "Expert" => ["stackingType" => ""],

            // "Rifle Marksman" => ["stackingType" => ""],
            // "Rifle Sharpshooter" => ["stackingType" => ""],
            // "Rifle Expert" => ["stackingType" => ""],
            //
            // "Grenade Marksman" => ["stackingType" => ""],
            // "Grenade Sharpshooter" => ["stackingType" => ""],
            // "Grenade Expert" => ["stackingType" => ""],
            //
            // "M-203 Marksman" => ["stackingType" => ""],
            // "M-203 Sharpshooter" => ["stackingType" => ""],
            // "M-203 Expert" => ["stackingType" => ""],
            //
            // "Machine Gun Marksman" => ["stackingType" => ""],
            // "Machine Gun Sharpshooter" => ["stackingType" => ""],
            // "Machine Gun Expert" => ["stackingType" => ""],
            //
            // "Recoilless Rifle Marksman" => ["stackingType" => ""],
            // "Recoilless Rifle Sharpshooter" => ["stackingType" => ""],
            // "Recoilless Rifle Expert" => ["stackingType" => ""],
            //
            // "Pistol Marksman" => ["stackingType" => ""],
            // "Pistol Sharpshooter" => ["stackingType" => ""],
            // "Pistol Expert" => ["stackingType" => ""],
            //
            // "Aeroweapons Marksman" => ["stackingType" => ""],
            // "Aeroweapons Sharpshooter" => ["stackingType" => ""],
            // "Aeroweapons Expert" => ["stackingType" => ""],
            //
            // "Tank Weapons Expert" => ["stackingType" => ""],
            // "Tank Weapons Marksman" => ["stackingType" => ""],
            // "Tank Weapons Sharpshooter" => ["stackingType" => ""],
            //
            // "Hydra-70 Marksman" => ["stackingType" => ""],
            // "Hydra-70 Sharpshooter" => ["stackingType" => ""],
            // "Hydra-70 Expert" => ["stackingType" => ""],
        ],
        "primarySpecialSkills" => [
            "Infantry Badge" => ["stackingType" => "", "stackingMaxCount" => 5],
            "Aviation Badge" => ["stackingType" => "", "stackingMaxCount" => 3],
            "Aircraft Crewman Badge" => ["stackingType" => "", "stackingMaxCount" => 3],
            "Flight Medic Badge" => ["stackingType" => "", "stackingMaxCount" => 1],
            // "Army Aviator Badge" => ["stackingType" => ""],
            // "Senior Army Aviator Badge" => ["stackingType" => ""],
            // "Master Army Aviator Badge" => ["stackingType" => ""],

            // "Aircraft Crewman Badge" => ["stackingType" => ""],
            // "Aircraft Senior Crewman Badge" => ["stackingType" => ""],
            // "Aircraft Master Crewman Badge" => ["stackingType" => ""],

            // "Flight Medic Badge" => ["stackingType" => ""],
        ],
        "secondarySpecialSkills" => [
            //specialSkills2
            "Combat Field Medical Badge" => ["stackingType" => "", "stackingMaxCount" => 1, "awardGroup" => "Field Medical Badge"],
            "Expert Field Medical Badge" => ["stackingType" => "", "stackingMaxCount" => 1, "awardGroup" => "Field Medical Badge"],
            // "Field Medical Badge" => ["stackingType" => ""],

            //specialSkills3
            // "Explosive Ordnance Disposal Badge" => ["stackingType" => ""],
            "Master Explosive Ordinance Disposal Badge" => ["stackingType" => "", "stackingMaxCount" => 1, "awardGroup" => "Explosive Ordnance Disposal Badge"],
            "Senior Explosive Ordnance Disposal Badge" => ["stackingType" => "", "stackingMaxCount" => 1, "awardGroup" => "Explosive Ordnance Disposal Badge"],
            "Basic Explosive Ordnance Disposal Badge" => ["stackingType" => "", "stackingMaxCount" => 1, "awardGroup" => "Explosive Ordnance Disposal Badge"],

            //specialSkills4
            "Master Gunner Badge" => ["stackingType" => "", "stackingMaxCount" => 1],
            "Air Assault Badge" => ["stackingType" => "", "stackingMaxCount" => 1],

            "Master Forward Air Controller Badge" => ["stackingType" => "", "stackingMaxCount" => 1, "awardGroup" => "Forward Air Controller Badge"],
            "Forward Air Controller Badge" => ["stackingType" => "", "stackingMaxCount" => 1, "awardGroup" => "Forward Air Controller Badge"],
            // "Forward Air Controller Badge" => ["stackingType" => ""],

            "Army Master Parachutist Badge" => ["stackingType" => "", "stackingMaxCount" => 6, "awardGroup" => "Army Parachutist Badge"],
            "Army Senior Parachutist Badge" => ["stackingType" => "", "stackingMaxCount" => 6, "awardGroup" => "Army Parachutist Badge"],
            "Army Parachutist Badge" => ["stackingType" => "", "stackingMaxCount" => 6, "awardGroup" => "Army Parachutist Badge"],
            // "Army Parachutist Badge" => ["stackingType" => ""],

            "Master High Altitude Low Opening (HALO) Freefall Badge" => ["stackingType" => "", "stackingMaxCount" => 6, "awardGroup" => "High Altitude Low Opening Badge"],
            "High Altitude Low Opening (HALO) Freefall Badge" => ["stackingType" => "", "stackingMaxCount" => 6, "awardGroup" => "High Altitude Low Opening Badge"],
            // "High Altitude Low Opening Badge" => ["stackingType" => ""],
        ],
    ];
}
