<?php

declare(strict_types=1);

namespace App\Entity\Regiment;

use App\Repository\Regiment\PlatoonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlatoonRepository::class)]
class Platoon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customName = null;

    #[ORM\ManyToOne(inversedBy: 'platoons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    /**
     * @var Collection<int, Section>
     */
    #[ORM\OneToMany(targetEntity: Section::class, mappedBy: 'platoon', orphanRemoval: false)]
    #[ORM\OrderBy(["sort" => "ASC"])]
    private Collection $sections;

    #[ORM\Column]
    private ?int $sort = null;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitleFull();
    }

    public function getTitleFull(): string
    {
        return implode("/", [$this->getTitle(), $this->company?->getTitleFull()]);
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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Section>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(Section $section): static
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setPlatoon($this);
        }

        return $this;
    }

    public function removeSection(Section $section): static
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getPlatoon() === $this) {
                $section->setPlatoon(null);
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
}
