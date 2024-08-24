<?php

declare(strict_types=1);

namespace App\Entity\Regiment;

use App\Repository\Regiment\BattalionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BattalionRepository::class)]
class Battalion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'battalion', orphanRemoval: false)]
    #[ORM\OrderBy(["sort" => "ASC"])]
    private Collection $companies;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customName = null;

    #[ORM\Column]
    private ?int $sort = null;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitleFull();
    }

    public function getTitleFull(): string
    {
        return $this->getTitle();
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

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->setBattalion($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getBattalion() === $this) {
                $company->setBattalion(null);
            }
        }

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
