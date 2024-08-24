<?php

declare(strict_types=1);

namespace App\Entity\Uniform;

use App\Entity\MilpacProfile;
use App\Entity\Regiment\ServiceBranch;
use App\Repository\Uniform\MilpacProfileUniformOverrideRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MilpacProfileUniformOverrideRepository::class)]
class MilpacProfileUniformOverride
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'milpacProfileUniformOverride')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MilpacProfile $milpacProfile = null;

    #[ORM\ManyToOne]
    private ?ServiceBranch $serviceBranch = null;

    #[ORM\ManyToOne]
    private ?ServiceBranch $preferredPrimarySpecialSkillServiceBranch = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preferredSecondarySpecialSkill1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preferredSecondarySpecialSkill2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preferredSecondarySpecialSkill3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preferredCrest = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $preferredBadge = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMilpacProfile(): ?MilpacProfile
    {
        return $this->milpacProfile;
    }

    public function setMilpacProfile(MilpacProfile $milpacProfile): static
    {
        $this->milpacProfile = $milpacProfile;

        return $this;
    }

    public function getServiceBranch(): ?ServiceBranch
    {
        return $this->serviceBranch;
    }

    public function setServiceBranch(?ServiceBranch $serviceBranch): static
    {
        $this->serviceBranch = $serviceBranch;

        return $this;
    }

    public function getPreferredPrimarySpecialSkillServiceBranch(): ?ServiceBranch
    {
        return $this->preferredPrimarySpecialSkillServiceBranch;
    }

    public function setPreferredPrimarySpecialSkillServiceBranch(?ServiceBranch $preferredPrimarySpecialSkillServiceBranch): static
    {
        $this->preferredPrimarySpecialSkillServiceBranch = $preferredPrimarySpecialSkillServiceBranch;

        return $this;
    }

    public function getPreferredSecondarySpecialSkill1(): ?string
    {
        return $this->preferredSecondarySpecialSkill1;
    }

    public function setPreferredSecondarySpecialSkill1(?string $preferredSecondarySpecialSkill1): static
    {
        $this->preferredSecondarySpecialSkill1 = $preferredSecondarySpecialSkill1;

        return $this;
    }

    public function getPreferredSecondarySpecialSkill2(): ?string
    {
        return $this->preferredSecondarySpecialSkill2;
    }

    public function setPreferredSecondarySpecialSkill2(?string $preferredSecondarySpecialSkill2): static
    {
        $this->preferredSecondarySpecialSkill2 = $preferredSecondarySpecialSkill2;

        return $this;
    }

    public function getPreferredSecondarySpecialSkill3(): ?string
    {
        return $this->preferredSecondarySpecialSkill3;
    }

    public function setPreferredSecondarySpecialSkill3(?string $preferredSecondarySpecialSkill3): static
    {
        $this->preferredSecondarySpecialSkill3 = $preferredSecondarySpecialSkill3;

        return $this;
    }

    public function getPreferredCrest(): ?string
    {
        return $this->preferredCrest;
    }

    public function setPreferredCrest(?string $preferredCrest): static
    {
        $this->preferredCrest = $preferredCrest;

        return $this;
    }

    public function getPreferredBadge(): ?string
    {
        return $this->preferredBadge;
    }

    public function setPreferredBadge(?string $preferredBadge): static
    {
        $this->preferredBadge = $preferredBadge;

        return $this;
    }
}
