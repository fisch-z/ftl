<?php

declare(strict_types=1);

namespace App\Entity\Regiment;

use App\Entity\MilpacProfile;
use App\Repository\Regiment\RankRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankRepository::class)]
class Rank
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $titleShort = null;

    #[ORM\Column]
    private ?int $sort = null;

    /**
     * @var Collection<int, MilpacProfile>
     */
    #[ORM\OneToMany(targetEntity: MilpacProfile::class, mappedBy: 'rank')]
    private Collection $milpacProfiles;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $rankImageUrl = null;

    #[ORM\Column(length: 255)]
    private ?string $rankType = null;

    public function __construct()
    {
        $this->milpacProfiles = new ArrayCollection();
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

    public function getTitleShort(): ?string
    {
        return $this->titleShort;
    }

    public function setTitleShort(string $titleShort): static
    {
        $this->titleShort = $titleShort;

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
            $milpacProfile->setRank($this);
        }

        return $this;
    }

    public function removeMilpacProfile(MilpacProfile $milpacProfile): static
    {
        if ($this->milpacProfiles->removeElement($milpacProfile)) {
            // set the owning side to null (unless already changed)
            if ($milpacProfile->getRank() === $this) {
                $milpacProfile->setRank(null);
            }
        }

        return $this;
    }

    public function getRankImageUrl(): ?string
    {
        return $this->rankImageUrl;
    }

    public function setRankImageUrl(string $rankImageUrl): static
    {
        $this->rankImageUrl = $rankImageUrl;

        return $this;
    }

    public function getRankType(): ?string
    {
        return $this->rankType;
    }

    public function setRankType(string $rankType): static
    {
        $this->rankType = $rankType;

        return $this;
    }
}
