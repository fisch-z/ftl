<?php

declare(strict_types=1);

namespace App\Entity\Regiment;

use App\Entity\MilpacProfile;
use App\Repository\Regiment\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customName = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Platoon $platoon = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceBranch $serviceBranch = null;

    /**
     * @var Collection<int, BilletAssignment>
     */
    #[ORM\OneToMany(targetEntity: BilletAssignment::class, mappedBy: 'section', orphanRemoval: false)]
    private Collection $billetAssignments;

    #[ORM\Column]
    private ?int $sort = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $bannerUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $practiceDay = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $practiceTime = null;

    /**
     * @var Collection<int, SectionPractice>
     */
    #[ORM\OneToMany(targetEntity: SectionPractice::class, mappedBy: 'section', orphanRemoval: false)]
    #[ORM\OrderBy(["dateTime" => "DESC"])]
    private Collection $sectionPractices;

    public function __construct()
    {
        $this->billetAssignments = new ArrayCollection();
        $this->sectionPractices = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitleFull();
    }

    public function getTitleFull(): string
    {
        return implode("/", [$this->getTitle(), $this->platoon?->getTitleFull()]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCustomName(): ?string
    {
        return $this->customName;
    }

    public function setCustomName(?string $customName): static
    {
        $this->customName = $customName;

        return $this;
    }

    public function getPlatoon(): ?Platoon
    {
        return $this->platoon;
    }

    public function setPlatoon(?Platoon $platoon): static
    {
        $this->platoon = $platoon;

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

    /**
     * @return Collection<int, BilletAssignment>
     */
    public function getBilletAssignments(): Collection
    {
        return $this->billetAssignments;
    }

    public function addBilletAssignment(BilletAssignment $billetAssignment): static
    {
        if (!$this->billetAssignments->contains($billetAssignment)) {
            $this->billetAssignments->add($billetAssignment);
            $billetAssignment->setSection($this);
        }

        return $this;
    }

    public function removeBilletAssignment(BilletAssignment $billetAssignment): static
    {
        if ($this->billetAssignments->removeElement($billetAssignment)) {
            // set the owning side to null (unless already changed)
            if ($billetAssignment->getSection() === $this) {
                $billetAssignment->setSection(null);
            }
        }

        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    public function getBannerUrl(): ?string
    {
        return $this->bannerUrl;
    }

    public function setBannerUrl(?string $bannerUrl): static
    {
        $this->bannerUrl = $bannerUrl;

        return $this;
    }

    public function getPracticeDay(): ?string
    {
        return $this->practiceDay;
    }

    public function setPracticeDay(?string $practiceDay): static
    {
        $this->practiceDay = $practiceDay;

        return $this;
    }

    public function getPracticeTime(): ?\DateTimeImmutable
    {
        return $this->practiceTime;
    }

    public function setPracticeTime(?\DateTimeImmutable $practiceTime): static
    {
        $this->practiceTime = $practiceTime;

        return $this;
    }

    /**
     * @return Collection<int, SectionPractice>
     */
    public function getSectionPractices(): Collection
    {
        return $this->sectionPractices;
    }

    public function addSectionPractice(SectionPractice $sectionPractice): static
    {
        if (!$this->sectionPractices->contains($sectionPractice)) {
            $this->sectionPractices->add($sectionPractice);
            $sectionPractice->setSection($this);
        }

        return $this;
    }

    public function removeSectionPractice(SectionPractice $sectionPractice): static
    {
        if ($this->sectionPractices->removeElement($sectionPractice)) {
            // set the owning side to null (unless already changed)
            if ($sectionPractice->getSection() === $this) {
                $sectionPractice->setSection(null);
            }
        }

        return $this;
    }

    public function getSectionLeader(): ?MilpacProfile
    {
        $billetAssignment = $this->getBilletAssignments()->findFirst(fn($i, BilletAssignment $a) => $a->getPosition()->getId() === 21);
        return $billetAssignment?->getMilpacProfiles()?->first() ?: null;
    }

    public function getAssistantSectionLeader(): ?MilpacProfile
    {
        $billetAssignment = $this->getBilletAssignments()->findFirst(fn($i, BilletAssignment $a) => $a->getPosition()->getId() === 22);
        return $billetAssignment?->getMilpacProfiles()?->first() ?: null;
    }
}
