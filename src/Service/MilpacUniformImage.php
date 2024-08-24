<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MilpacProfile;
use App\Entity\MilpacProfileAward;
use App\Entity\MilpacProfileAwardList;
use App\Entity\Regiment\BilletAssignment;
use App\Repository\Regiment\BilletAssignmentRepository;
use App\Repository\Regiment\BilletPositionRepository;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;
use Symfony\Contracts\Cache\CacheInterface;

class MilpacUniformImage
{
    public ImageInterface $image;
    public ImageInterface $imageUniform;
    public ImageInterface $imageMedals;
    private int $width;
    private int $totalHeight;
    private int $uniformHeight;
    private int $medalRackHeight;

    private int $borderWidth = 20;
    private int $imageSpacing = 0;
    // private int $imageSpacing = 20;
    private int $widthInner;
    private int $uniformHeightInner;
    private int $medalRackHeightInner;

    private MilpacProfileAwardList $awardList;

    public function __construct(
        private readonly MilpacProfile              $milpacProfile,
        private readonly ImageManager               $imageManager,
        private readonly string                     $basePath,
        private readonly string                     $uniformAssetsPath,
        private readonly BilletAssignmentRepository $billetAssignmentRepository,
        private readonly BilletPositionRepository   $billetPositionRepository,
        private readonly CacheInterface             $cache,
    )
    {
        // $this->image = $this->imageManager->create(837, 1045);
        $this->image = $this->imageManager->create(837, 1025);
        foreach ([
                     "{$this->uniformAssetsPath}/base-uniform-{$this->milpacProfile->getServiceBranch()}.png",
                     "{$this->uniformAssetsPath}/base-uniform-default.png",
                 ] as $path) {
            if (file_exists($path)) {
                $this->imageUniform = $this->imageManager->read($path);
                break;
            }
        }
        foreach ([
                     "{$this->uniformAssetsPath}/base-medals-{$this->milpacProfile->getServiceBranch()}.png",
                     "{$this->uniformAssetsPath}/base-medals-default.png",
                 ] as $path) {
            if (file_exists($path)) {
                $this->imageMedals = $this->imageManager->read($path);
                break;
            }
        }
        // $this->image = $this->imageManager->read("{$this->uniformAssetsPath}/base-alternative.png");
        $this->width = $this->image->width();
        $this->totalHeight = $this->image->height();
        $this->uniformHeight = $this->imageUniform->height();
        $this->medalRackHeight = $this->imageMedals->height();
        $this->widthInner = $this->width - $this->borderWidth * 2;
        $this->uniformHeightInner = $this->uniformHeight - $this->borderWidth * 2;
        $this->medalRackHeightInner = $this->medalRackHeight - $this->borderWidth * 2;
        $this->awardList = $this->milpacProfile->getAwardList();

        $this->placeNameTag();
        $this->placeCopyright();

        $this->placeServiceYearStripes();

        $unitCitationsOffsetY = $this->placeUnitCitation();
        $this->placeActiveBilletCrest($unitCitationsOffsetY);

        $specialSkillAwards = $this->awardList->getPrimarySpecialSkills();
        $this->placeRibbons($specialSkillAwards ? array_shift($specialSkillAwards) : null);

        // place chest overlay before medals because of James Krazee Foster medal
        foreach ([
                     "{$this->uniformAssetsPath}/chest-overlay-{$this->milpacProfile->getServiceBranch()}.png",
                     "{$this->uniformAssetsPath}/chest-overlay-default.png",
                 ] as $path) {
            if (file_exists($path)) {
                $this->imageUniform->place($path);
                break;
            }
        }

        $this->placePinBoardAndRightChest($specialSkillAwards);

        $this->placeMedals();
        $this->imageUniform->place("{$this->uniformAssetsPath}/regiment.png");
        $this->imageUniform->place("{$this->uniformAssetsPath}/rank/{$this->milpacProfile->getRankShort()}.png");
        $this->placeColarPin();
        $this->placeShoulderCord();
        $this->placeTabs();
        if ($this->milpacProfile->isTrooper() || $this->milpacProfile->isNonCommissionedOfficer()) {
            $this->imageUniform->place("{$this->uniformAssetsPath}/trooper-schoulder.png");
        }
        // $this->image = $this->imageManager->create(837, 1045);
        // $image = $this->imageManager->create(837, 1045);
        // $image->place($this->image);
        // $this->image=$image;
        // $this->image->pad(837, 1045, "transparent", "top-left");
        $this->image->place($this->imageUniform);
        $this->image->place($this->imageMedals, "top-left", 0, $this->uniformHeight + $this->imageSpacing);
    }

    public function toTiff()
    {
        return $this->image->toTiff();
    }

    public function toPng()
    {
        return $this->image->toPng();
    }

    public function toWebp()
    {
        return $this->image->toWebp();
    }

    public function toJpeg()
    {
        return $this->image->toJpeg();
    }

    private function placeServiceYearStripes(): void
    {
        if ($this->awardList->getYearsInServiceStripesCount()) {
            $prefix = $this->milpacProfile->isTrooper() || $this->milpacProfile->isNonCommissionedOfficer() ? "trooper" : "officer";
            $this->imageUniform->place("{$this->uniformAssetsPath}/serviceStripes/$prefix-{$this->awardList->getYearsInServiceStripesCount()}.png");
        }
    }

    private function placeColarPin(): void
    {
        $filesToTry = [];
        $prefix = $this->milpacProfile->isTrooper() || $this->milpacProfile->isNonCommissionedOfficer() ? "trooper" : "officer";
        if ($this->milpacProfile->getServiceBranch()->getTitle() !== "Recruit") {
            $primaryBilletAssignment = $this->milpacProfile->getPrimaryBilletAssignment();
            if (preg_match("@Aide to the (.*)@", $primaryBilletAssignment->getPosition()->getTitle(), $matches)) {
                // if position is an aide, we have to find the person to whom this is an aide and then decided the amount of starts based on that
                $billetPosition = $this->billetPositionRepository->findOneBy(["title" => $matches[1]]);
                $aideToBilletAssignment = $billetPosition->getBilletAssignments()->first();
                if ($aideToBilletAssignment) {
                    $aidToMilpacProfile = $aideToBilletAssignment->getMilpacProfilesWithPrimary()->first();
                    if ($aidToMilpacProfile) {
                        $filesToTry[] = "aide-to-{$aidToMilpacProfile->getRankShort()}.png";
                    }
                }
            }
            $platoon = $primaryBilletAssignment->getPlatoon()->getTitle();
            $serviceBranch = $this->milpacProfile->getServiceBranch();
            $filesToTry[] = "{$prefix}-{$platoon}.png";
            $filesToTry[] = "{$prefix}-{$serviceBranch}.png";
        }
        $filesToTry[] = "{$prefix}-default.png";
        foreach ($filesToTry as $file) {
            $path = "{$this->uniformAssetsPath}/colarPins/$file";
            if (file_exists($path)) {
                $this->imageUniform->place($path);
                break;
            }
        }
    }

    private function placeShoulderCord(): void
    {
        $isAide = $this->milpacProfile->isAide();
        if ($this->milpacProfile->getServiceBranch()->getTitle() !== "Recruit" && ($isAide || !$this->milpacProfile->isOfficer())) {
            $file = $isAide ? "Aide" : $this->milpacProfile->getServiceBranch()->getTitle();
            $path = "{$this->uniformAssetsPath}/shoulderCord/{$file}.png";
            if (file_exists($path)) {
                $this->imageUniform->place($path);
            }
        }
    }

    private function placeTabs(): void
    {
        $x = 0;
        foreach ($this->awardList->getTabs() as $tab) {
            $this->placeAwardWithStackingOverlay(
                $this->imageUniform,
                $tab,
                "top-right",
                54,
                143 + 30 * $x++
            );
        }
    }

    private function placeCopyright(): void
    {
        //
        $width = 200;
        $height = $this->borderWidth;
        $offsetX = $this->width / 2;
        $offsetY = $this->uniformHeight - 12;
        $this->imageUniform->text("© 7th Cavalry Regiment", (int)$offsetX, (int)$offsetY, function (FontFactory $font) use ($height, $width) {
            // $font->filename("$folder/ARIALNB.TTF");
            $font->filename("{$this->uniformAssetsPath}/Arial_Condensed_Bold_Regular.ttf");
            $font->size(12);
            $font->color('1e1507');
            // $font->stroke('ff5500', 2);
            $font->align('center');
            $font->valign('middle');
            $font->lineHeight(2);
            $font->angle(0);
            $font->wrap($width);
        });
    }

    private function placeNameTag(): void
    {
        $name = strtoupper(preg_replace("@^(.*?)\..*$@", "$1", $this->milpacProfile->getUsername()));
        $offsetX = 254;
        $offsetY = 328;
        $height = 34;
        if (strlen($name) > 15) {
            $name = substr($name, 0, 14) . ".";
        }
        $width = strlen($name) > 12 ? 156 : (strlen($name) > 7 ? 130 : 100);

        $this->imageUniform->drawRectangle($offsetX - ($width / 2), $offsetY - ($height / 2), function (RectangleFactory $rectangle) use ($width, $height) {
            $rectangle->size($width, $height);
            $rectangle->background('red');
            $rectangle->border('#e1dfdd', 1);
        });
        $this->imageUniform->drawRectangle($offsetX - ($width / 2) + 1, $offsetY - ($height / 2) + 1, function (RectangleFactory $rectangle) use ($width, $height) {
            $rectangle->size($width - 2, $height - 2);
            $rectangle->background('#27272d');
            $rectangle->border('#767370', 1);
        });
        $this->imageUniform->text($name, $offsetX, $offsetY, function (FontFactory $font) use ($height, $width) {
            $font->filename("{$this->uniformAssetsPath}/Arial_Condensed_Bold_Regular.ttf");
            $font->size(18);
            $font->color('fff');
            // $font->stroke('ff5500', 2);
            $font->align('center');
            $font->valign('middle');
            $font->lineHeight(1.6);
            $font->angle(0);
            $font->wrap($width);
        });
    }


    private function placeRibbons($primarySpecialSkill = null): void
    {
        $awards = $this->awardList->getRibbons();
        $count = count($awards);
        $height = 13;
        $width = 43;
        $spacing = 1;
        // $offsetY = $this->medalRackHeight + 412;
        $offsetY = 412;
        $offsetX = 170;
        $x = 0;
        $y = 0;
        if ($count < 12) {
            $offsetX += 21;
            $extraOffsetX = 0;
            if ($count == 2) {
                $extraOffsetX = 20;
            }
            if ($count == 1) {
                $extraOffsetX = 43;
            }

            foreach ($awards as $i => $award) {
                if ($x == 3) {
                    $x = 0;
                    $y++;
                    if ($count - $i == 2) {
                        $extraOffsetX = $width / 2;
                    } elseif ($count - $i == 1) {
                        $extraOffsetX = $width;
                    }
                }
                $this->placeAwardWithStackingOverlay(
                    $this->imageUniform,
                    $award,
                    'bottom-right',
                    (int)($offsetX + $extraOffsetX + $x * ($width + $spacing)),
                    (int)($offsetY + $y * ($height + $spacing)),
                    $width,
                    $height,
                );
                $x++;
            }
        } else {
            $perRow = 4;
            foreach ($awards as $award) {
                if ($x == $perRow) {
                    $x = 0;
                    $y++;
                    if ($y === 2) {
                        $perRow--;
                    }
                    if ($y === 5) {
                        $perRow--;
                    }
                    // if ($y === 8) {
                    //     $perRow--;
                    // }
                }
                $this->placeAwardWithStackingOverlay(
                    $this->imageUniform,
                    $award,
                    'bottom-right',
                    (int)($offsetX + $x * ($width + $spacing)),
                    (int)($offsetY + $y * ($height + $spacing)),
                    $width,
                    $height
                );
                $x++;
            }
        }

        if ($primarySpecialSkill) {
            $specialSkillOffsetY = ($offsetY + ($y + 1) * ($height + $spacing)) - 3;
            $offsetXModifier = $y > 5 ? 20 : min(max(0, $y - 1), 3) * 12;

            $path = "{$this->basePath}/{$primarySpecialSkill->getImageFileName()}";
            $image = $this->imageManager->read($path);
            $offsetX = 252 - ($image->width() / 2);
            // $offsetX = 220 - ($image->width() / 2);
            // dd($y);
            // 769
            // $offsetXModifier = min(40, ($specialSkillOffsetY - 400) / 3);
            $this->imageUniform->place($path, "bottom-right", (int)($offsetX - $offsetXModifier), (int)$specialSkillOffsetY);
        }
    }

    private function placeUnitCitation(): int
    {
        $awards = $this->awardList->getUnitCitations();
        $count = count($awards);
        $height = 17 + 1;
        $width = 43 + 1;
        // $offsetY = 412 + 315;
        $offsetY = 412;
        $offsetX = 190 + $width * 2;
        $x = 0;
        $y = 0;
        $extraOffsetX = 0;
        if ($count == 2) {
            $extraOffsetX = 20;
        }
        if ($count == 1) {
            $extraOffsetX = 43;
        }

        foreach (array_reverse($awards) as $i => $award) {
            if ($x == 3) {
                $x = 0;
                $y++;
                if ($count - $i == 2) {
                    $extraOffsetX = $width / 2;
                } elseif ($count - $i == 1) {
                    $extraOffsetX = $width;
                }
            }

            $this->placeAwardWithStackingOverlay(
                $this->imageUniform,
                $award,
                'bottom-left',
                (int)($offsetX - ($extraOffsetX + $x * $width)),
                (int)($offsetY + $y * $height),
                $width,
                $height,
            );
            $x++;
        }
        return $offsetY + $y * $height;
    }

    private function placeAwardWithStackingOverlay(
        Image              $image,
        MilpacProfileAward $award,
        string             $position,
        int                $offsetX,
        int                $offsetY,
        int                $awardWidth = null,
        int                $awardHeight = null,
        int                $overlayOffsetYExtra = 0,
    ): void
    {
        if (!file_exists("{$this->basePath}/{$award->getImageFileName()}")) {
            return;
        }
        $image->place("{$this->basePath}/{$award->getImageFileName()}", $position, $offsetX, $offsetY);
        $path = "{$this->basePath}/{$award->getStackingOverlayFileName()}";
        if ($award->getStackingOverlayFileName() && file_exists($path)) {
            $overlayWidth = 43;
            $overlayHeight = 13;
            $overlayOffsetX = $offsetX + ($awardWidth - $overlayWidth) / 2;
            $overlayOffsetY = $offsetY + ($awardHeight - $overlayHeight) / 2 + $overlayOffsetYExtra;
            $image->place($path, $position, (int)$overlayOffsetX, (int)$overlayOffsetY);
        }
    }

    private function placeMedals(): void
    {
        $medals = $this->awardList->getMedals();
        foreach ($medals as $i => $award) {
            if ($award->getName() === "James Krazee Foster Lifetime Achievement Medal") {
                $this->imageUniform->place("{$this->basePath}/{$award->getImageFileName()}", "top-left", 220, 22);
                unset($medals[$i]);
            }
        }
        $width = $this->widthInner;
        // $height = $this->medalRackHeightInner;
        $offsetX = $this->borderWidth;
        // $offsetY = $this->uniformHeight + $this->borderWidth;
        $offsetY = $this->borderWidth;
        $medalWidth = 70;
        $medalHeight = 120;
        $medalsPerRow = 12;
        $medalSpacing = ($width - $medalWidth * $medalsPerRow - 14) / $medalsPerRow;
        $stack = count($medals) > $medalsPerRow * 2;
        $extraOffsetX = 0;
        // $this->debugBox($offsetX, $offsetY, $width, $height);
        $x = 0;
        $y = 0;
        $y2 = 0;
        foreach ($medals as $award) {
            if (floor($x) == $medalsPerRow) {
                if ($stack) {
                    $medalsPerRow--;
                    $y2++;
                    $y += .33;
                    $x = .5 * $y2;
                } else {
                    $x = 0;
                    $y++;
                }
            }
            $_offsetX = $offsetX + 4 + $x * ($medalWidth + $medalSpacing) + $extraOffsetX;
            $_offsetY = $offsetY + 18 + $y * ($medalHeight + $medalSpacing + 18);

            // $this->debugBox($_offsetX, $_offsetY, $medalWidth, $medalHeight);
            $this->placeAwardWithStackingOverlay(
                $this->imageMedals,
                $award,
                'top-left',
                (int)$_offsetX,
                (int)$_offsetY,
                $medalWidth,
                $medalHeight,
                -45
            );
            $x++;
        }
    }

    private function getActiveBilletCrests(): array
    {
        $filesToCheck = [
            "primary" => [],
            "1ic" => [],
            "2ic" => [],
            "lead" => [],
            "senior" => [],
            "clerk" => [],
        ];
        foreach ([$this->milpacProfile->getPrimaryBilletAssignment(), ...$this->milpacProfile->getBilletAssignments()] as $billetAssignment) {
            /** @var BilletAssignment $billetAssignment */
            $prefix = "clerk";
            foreach ($filesToCheck as $k => $arr) {
                if (str_contains(strtolower($billetAssignment->getPosition()->getTitle()), $k)) {
                    $prefix = $k;
                    break;
                }
            }
            $filesToCheck[$prefix][] = $billetAssignment->getPlatoon()->getTitle();
            $filesToCheck[$prefix][] = $billetAssignment->getCompany()->getTitle() . "-" . $billetAssignment->getPlatoon()->getTitle();
            $filesToCheck[$prefix][] = $billetAssignment->getCompany()->getTitle() . "-" . $billetAssignment->getPlatoon()->getTitle() . "-" . $billetAssignment->getSection()->getTitle();
        }

        $_preferredCrest = $this->milpacProfile->getMilpacProfileUniformOverride()?->getPreferredCrest();
        $preferredCrest = null;
        $return = [];
        foreach ($filesToCheck as $files) {
            foreach ($files as $file) {
                $path = realpath("{$this->uniformAssetsPath}/crests/$file.png");
                if ($path && file_exists($path)) {
                    if ($_preferredCrest === $file) {
                        $preferredCrest = $path;
                    } else {
                        $return[$file] = $path;
                    }
                }
            }
        }
        return array_values(array_filter([$preferredCrest, ...array_values($return)]));
    }

    private function placeActiveBilletCrest(int $unitCitationsOffsetY): void
    {
        $crests = $this->getActiveBilletCrests();
        if ($crests) {
            $this->imageUniform->place($crests[0], 'bottom-left', 230, $unitCitationsOffsetY + 15);
        }
    }

    private function renderWeaponCertificationsBadges(int $maxRockers = 100): array
    {
        $weaponCertifications = $this->awardList->getWeaponCertifications();
        // $weaponCertificationsHeight = 0;
        // foreach ($weaponCertifications as $award) {
        //     $weaponCertificationsHeight = max($weaponCertificationsHeight, count($award->getRows()));
        // }

        $images = [];
        $badgeWidth = 90;
        $badgeHeight = 100;
        $addonHeight = 28;
        // $totalHeight = $badgeHeight + $addonHeight * $weaponCertificationsHeight;
        foreach ($weaponCertifications as $award) {
            $rows = [];
            foreach ($award->getRows() as $row) {
                $name = str_replace(" {$award->getName()}", "", $row["originalAwardName"]);
                $rows[$name] = $name;
            }
            $rows = array_values($rows);
            $totalHeight = $badgeHeight + $addonHeight * count($rows);
            $image = $this->imageManager->create($badgeWidth, $totalHeight);

            $image->place("{$this->basePath}/{$award->getImageFileName()}");
            // foreach ($rows as $i => $name) {
            for ($i = 0; $i < min($maxRockers, count($rows)); $i++) {
                $name = $rows[$i];
                $path = "{$this->uniformAssetsPath}/weaponCertifications/addons/{$name}.png";
                $image->place(
                    $path,
                    "top-left",
                    0,
                    $badgeHeight + 5 + $addonHeight * ($i - 1)
                );
            }
            $images[] = $image;
        }
        return $images;
    }


    private function getBadges(): array
    {
        $awardedBadgesStackable = [];
        foreach ($this->awardList->getAwardedBadgesStackable() as $award) {
            $path = "{$this->basePath}/{$award->getImageFileName()}";
            if (file_exists($path)) {
                $awardedBadgesStackable[$award->getName()] = $path;
            }
        }
        $billetedBadges = [
            "preferred" => [],
            "primary" => [],
            "1ic" => [],
            "2ic" => [],
            "lead" => [],
            "senior" => [],
            "clerk" => [],
        ];
        $preferredBadgeName = $this->milpacProfile->getMilpacProfileUniformOverride()?->getPreferredBadge();
        foreach ([$this->milpacProfile->getPrimaryBilletAssignment(), ...$this->milpacProfile->getBilletAssignments()] as $billetAssignment) {
            /** @var BilletAssignment $billetAssignment */
            $prefix = "clerk";
            foreach ($billetedBadges as $k => $arr) {
                if (str_contains($billetAssignment->getPosition()->getTitle(), $k)) {
                    $prefix = $k;
                    break;
                }
            }
            $badgeName = "";
            if ($billetAssignment->getPlatoon()->getTitle() === "Command Staff" && in_array($billetAssignment->getSection()->getTitle(), ["Recruiting Oversight", "Command Staff"]) && !$this->milpacProfile->isAide()) {
                $badgeName = "General Staff Badge";
            } elseif ($billetAssignment->getServiceBranch()->getTitle() === "Military Police" && $billetAssignment->getCompany()->getTitle() === "Support") {
                $badgeName = "Military Police Badge";
            } elseif ($billetAssignment->getPlatoon()->getTitle() === "RRD" && $billetAssignment->getCompany()->getTitle() === "Support") {
                $badgeName = "Recruiter Badge";
            } elseif ($billetAssignment->getPlatoon()->getTitle() === "RTC" && $billetAssignment->getSection()->getTitle() === "Drill Instructor" && $billetAssignment->getCompany()->getTitle() === "Support") {
                $badgeName = "Drill Instructor Badge";
            } elseif ($billetAssignment->getPlatoon()->getTitle() === "S7" && $billetAssignment->getCompany()->getTitle() === "Support") {
                $badgeName = "Instructor Badge";
            } elseif (isset($awardedBadgesStackable["Mission Controller Badge"]) && $billetAssignment->getPlatoon()->getTitle() === "S3" && $billetAssignment->getCompany()->getTitle() === "Support") {
                // there is no billeted "Mission Controller Badge", but if you are S3 and have been awarded, it should be prioritized
                $badgeName = "Mission Controller Badge";
            }
            if ($badgeName) {
                if ($badgeName === $preferredBadgeName) {
                    $prefix = "preferred";
                }
                // $billetedBadges[$prefix][$k] = $k;
                $billetedBadges[$prefix][$badgeName] = "{$this->uniformAssetsPath}/billetedBadges/$badgeName.png";
            }
        }

        $badges = [];
        foreach ($billetedBadges as $billetedBadgesByPriority) {
            foreach ($billetedBadgesByPriority as $badgeName => $path) {
                if (isset($awardedBadgesStackable[(string)$badgeName])) {
                    $path = $awardedBadgesStackable[(string)$badgeName];
                    unset($awardedBadgesStackable[(string)$badgeName]);
                }
                $badges[] = $path;
            }
        }
        $primaryBadge = array_shift($badges);
        foreach ($awardedBadgesStackable as $path) {
            $badges[] = $path;
        }
        foreach ($this->awardList->getAwardedBadges() as $award) {
            $path = "{$this->basePath}/{$award->getImageFileName()}";
            $badges[] = $path;
        }
        return [$primaryBadge, $badges];
    }

    private function getPermanentCrests(): array
    {
        $billetedCrests = $this->getActiveBilletCrests();
        $matchedDepartments = [];
        foreach ($this->awardList->getArmedForcesServiceMedal()?->getRows() ?: [] as $row) {
            $pattern = implode("|", ["JAG", "MP", "RTC", "S1", "S2", "S3", "S5", "S6", "S7", "WAG", "RRD"]);
            if (preg_match("@^(For\s+|)($pattern)\.?\s+(from|dep|Dep|\d\d?\w\w\w\d\d)@", trim($row["details"]), $matches)) {
                $k = $matches[2];
                $k = match ($k) {
                    "RTC" => "Support-RTC",
                    default => $k,
                };
                $path = \BASE_PATH . "/public/appAssets/uniform/crests/{$k}.png";
                // if (file_exists($path) && $path !== $billetedCrests[0]) {
                if (file_exists($path) && !in_array(realpath($path), $billetedCrests)) {
                    $matchedDepartments[$k] = realpath($path);
                }
            }
        }
        return array_values($matchedDepartments);
    }

    private function placePinBoardAndRightChest($specialSkillAwards): void
    {
        $extraOffset = 0;
        // $extraOffset = 155;
        $weaponCertificationBadges = $this->renderWeaponCertificationsBadges();
        $weaponCertificationBadgesChest = $this->renderWeaponCertificationsBadges(3);
        $offsetX = $this->borderWidth + $extraOffset;
        foreach ($weaponCertificationBadges as $image) {
            $this->imageUniform->place(
                $image,
                "bottom-left",
                $offsetX,
                // $this->medalRackHeight + $this->borderWidth * 2 - 25
                $this->borderWidth * 2 - 25
            );
            $offsetX += 70;
        }

        $specialSkillAwards2 = $this->awardList->getSecondarySpecialSkills();
        $specialSkillAwards2Primary = [];
        for ($i = 0; $i < 3; $i++) {
            $image = null;
            if ($specialSkillAwards2) {
                /** @var MilpacProfileAward $award */
                $award = array_shift($specialSkillAwards2);
                $specialSkillAwards2Primary[] = $this->imageManager->read("{$this->basePath}/{$award->getImageFileName()}");
            } elseif ($weaponCertificationBadgesChest) {
                /** @var Image $image */
                $image = array_shift($weaponCertificationBadgesChest);
                $specialSkillAwards2Primary[] = $image->resize(50, (int)($image->height() * 50 / $image->width()));
            }
        }
        $specialSkillAwards2Primary = match (count($specialSkillAwards2Primary)) {
            default => $specialSkillAwards2Primary,
            1 => [null, $specialSkillAwards2Primary[0], null],
            2 => [$specialSkillAwards2Primary[0], null, $specialSkillAwards2Primary[1]],
        };
        $offsetX = $this->borderWidth + 478;
        foreach ($specialSkillAwards2Primary as $image) {
            if ($image) {
                /** @var Image $image */
                $this->imageUniform->place(
                    $image,
                    "top-left",
                    (int)($offsetX + 56 / 2 - $image->width() / 2),
                    305,
                );
            }
            $offsetX += 56;
        }

        [$primaryBadge, $badges] = $this->getBadges();
        if ($primaryBadge) {
            $badgeImage = $this->imageManager->read($primaryBadge);
            $this->imageUniform->place(
                $badgeImage,
                "bottom-right",
                (int)(257 - $badgeImage->width() / 2),
                // $this->medalRackHeight + $this->borderWidth * 2 + 218 - $badgeImage->height() / 2,
                (int)($this->borderWidth * 2 + 218 - $badgeImage->height() / 2),
            );
        }

        $compositeImage = null;
        $count = count($specialSkillAwards);
        // $perComposite = $count === 4 ? 2 : min(3, $count);
        $perComposite = min(2, $count);
        foreach ($specialSkillAwards as $i => $award) {
            $x = $i % $perComposite;
            $compositeImage = $x === 0 ? $this->imageManager->create(105, 59 * $perComposite) : $compositeImage;
            $compositeImage->place(
                "{$this->basePath}/{$award->getImageFileName()}",
                "top-left",
                0,
                5 + 49 * $x
            );
            if ($x === $perComposite - 1 || $i === $count - 1) {
                $badges[] = $compositeImage;
            }
        }

        $activeCrests = $this->getActiveBilletCrests();
        array_shift($activeCrests);
        $permanentCrests = $this->getPermanentCrests();
        $allCrests = [...$activeCrests, ...$permanentCrests];

        $compositeImage = null;
        $count = count($specialSkillAwards2) + count($allCrests);
        // $perComposite = $count === 4 ? 2 : min(3, $count);
        $perComposite = min(2, $count);
        $i = 0;
        foreach (["specialSkill" => $specialSkillAwards2, "crest" => $allCrests] as $type => $list) {
            foreach ($list as $award) {
                $x = $i % $perComposite;
                $compositeImage = $x === 0 ? $this->imageManager->create(70, 50 * $perComposite) : $compositeImage;
                $compositeImage->place(
                    is_string($award) ? $award : "{$this->basePath}/{$award->getImageFileName()}",
                    "top-left",
                    ($type === "crest" ? 10 : 0),
                    ($type === "crest" ? 3 : -5) + 50 * $x
                );
                if ($x === $perComposite - 1 || $i === $count - 1) {
                    $badges[] = $compositeImage;
                }
                $i++;
            }
        }

        $offsetX = $this->borderWidth + 10 + $extraOffset;
        foreach ($badges as $badgeImage) {
            $badgeImage = is_string($badgeImage) ? $this->imageManager->read($badgeImage) : $badgeImage;
            $this->imageUniform->place(
                $badgeImage,
                "bottom-right",
                (int)$offsetX,
                // $this->medalRackHeight + $this->borderWidth * 2 + 40 - $badgeImage->height() / 2,
                (int)($this->borderWidth * 2 + 40 - $badgeImage->height() / 2),
            );
            $offsetX += $badgeImage->width() + 5;
        }
    }
}
