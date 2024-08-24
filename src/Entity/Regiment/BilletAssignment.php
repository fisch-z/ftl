<?php

declare(strict_types=1);

namespace App\Entity\Regiment;

use App\Entity\MilpacProfile;
use App\Repository\Regiment\BilletAssignmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BilletAssignmentRepository::class)]
class BilletAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true, nullable: false)]
    private ?int $milpacId = null;

    #[ORM\Column(length: 255)]
    private ?string $milpacTitle = null;

    #[ORM\ManyToOne(inversedBy: 'billetAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Section $section = null;

    /**
     * @var Collection<int, MilpacProfile>
     */
    #[ORM\ManyToMany(targetEntity: MilpacProfile::class, mappedBy: 'billetAssignments')]
    private Collection $milpacProfiles;

    /**
     * @var Collection<int, MilpacProfile>
     */
    #[ORM\OneToMany(targetEntity: MilpacProfile::class, mappedBy: 'primaryBilletAssignment')]
    private Collection $milpacProfilesWithPrimary;

    #[ORM\ManyToOne(inversedBy: 'billetAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BilletPosition $position = null;

    public function __construct()
    {
        $this->milpacProfiles = new ArrayCollection();
        $this->milpacProfilesWithPrimary = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getPosition()->getTitle() . " - " . $this->getSection()->getTitleFull();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMilpacId(): ?int
    {
        return $this->milpacId;
    }

    public function setMilpacId(int $milpacId): static
    {
        $this->milpacId = $milpacId;

        return $this;
    }

    public function getMilpacTitle(): ?string
    {
        return $this->milpacTitle;
    }

    public function setMilpacTitle(string $milpacTitle): static
    {
        $this->milpacTitle = $milpacTitle;

        return $this;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): static
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @return Collection<int, MilpacProfile>
     */
    public function getMilpacProfiles(): Collection
    {
        return $this->milpacProfiles;
    }

    public function addMilpacProfile(MilpacProfile $milpacProfile): static
    {
        if (!$this->milpacProfiles->contains($milpacProfile)) {
            $this->milpacProfiles->add($milpacProfile);
            $milpacProfile->addBilletAssignment($this);
        }

        return $this;
    }

    public function removeMilpacProfile(MilpacProfile $milpacProfile): static
    {
        if ($this->milpacProfiles->removeElement($milpacProfile)) {
            $milpacProfile->removeBilletAssignment($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, MilpacProfile>
     */
    public function getMilpacProfilesWithPrimary(): Collection
    {
        return $this->milpacProfilesWithPrimary;
    }

    public function addMilpacProfilesWithPrimary(MilpacProfile $milpacProfilesWithPrimary): static
    {
        if (!$this->milpacProfilesWithPrimary->contains($milpacProfilesWithPrimary)) {
            $this->milpacProfilesWithPrimary->add($milpacProfilesWithPrimary);
            $milpacProfilesWithPrimary->setPrimaryBilletAssignment($this);
        }

        return $this;
    }

    public function removeMilpacProfilesWithPrimary(MilpacProfile $milpacProfilesWithPrimary): static
    {
        if ($this->milpacProfilesWithPrimary->removeElement($milpacProfilesWithPrimary)) {
            // set the owning side to null (unless already changed)
            if ($milpacProfilesWithPrimary->getPrimaryBilletAssignment() === $this) {
                $milpacProfilesWithPrimary->setPrimaryBilletAssignment(null);
            }
        }

        return $this;
    }

    public function getPlatoon(): Platoon
    {
        return $this->getSection()->getPlatoon();
    }

    public function getCompany(): Company
    {
        return $this->getPlatoon()->getCompany();
    }

    public function getBattalion(): Battalion
    {
        return $this->getCompany()->getBattalion();
    }

    public function getServiceBranch(): ServiceBranch
    {
        return $this->getSection()->getServiceBranch();
    }

    public function getPosition(): ?BilletPosition
    {
        return $this->position;
    }

    public function setPosition(?BilletPosition $position): static
    {
        $this->position = $position;

        return $this;
    }
}
