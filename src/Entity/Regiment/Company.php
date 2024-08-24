<?php

declare(strict_types=1);

namespace App\Entity\Regiment;

use App\Repository\Regiment\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customName = null;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Battalion $battalion = null;

    /**
     * @var Collection<int, Platoon>
     */
    #[ORM\OneToMany(targetEntity: Platoon::class, mappedBy: 'company', orphanRemoval: false)]
    #[ORM\OrderBy(["sort" => "ASC"])]
    private Collection $platoons;

    #[ORM\Column]
    private ?int $sort = null;

    public function __construct()
    {
        $this->platoons = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitleFull();
    }

    public function getTitleFull(): string
    {
        return implode("/", [$this->getTitle(), $this->battalion?->getTitleFull()]);
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

    public function setCustomName(string $customName): static
    {
        $this->customName = $customName;

        return $this;
    }

    public function getBattalion(): ?Battalion
    {
        return $this->battalion;
    }

    public function setBattalion(?Battalion $battalion): static
    {
        $this->battalion = $battalion;

        return $this;
    }

    /**
     * @return Collection<int, Platoon>
     */
    public function getPlatoons(): Collection
    {
        return $this->platoons;
    }

    public function addPlatoon(Platoon $platoon): static
    {
        if (!$this->platoons->contains($platoon)) {
            $this->platoons->add($platoon);
            $platoon->setCompany($this);
        }

        return $this;
    }

    public function removePlatoon(Platoon $platoon): static
    {
        if ($this->platoons->removeElement($platoon)) {
            // set the owning side to null (unless already changed)
            if ($platoon->getCompany() === $this) {
                $platoon->setCompany(null);
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
