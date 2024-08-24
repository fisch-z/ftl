<?php

declare(strict_types=1);

namespace App\Entity\Regiment;

use App\Repository\Regiment\BilletPositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BilletPositionRepository::class)]
class BilletPosition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?int $sort = null;

    /**
     * @var Collection<int, BilletAssignment>
     */
    #[ORM\OneToMany(targetEntity: BilletAssignment::class, mappedBy: 'position')]
    private Collection $billetAssignments;

    public function __construct()
    {
        $this->billetAssignments = new ArrayCollection();
    }

    public function __toString(): string
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
            $billetAssignment->setPosition($this);
        }

        return $this;
    }

    public function removeBilletAssignment(BilletAssignment $billetAssignment): static
    {
        if ($this->billetAssignments->removeElement($billetAssignment)) {
            // set the owning side to null (unless already changed)
            if ($billetAssignment->getPosition() === $this) {
                $billetAssignment->setPosition(null);
            }
        }

        return $this;
    }
}
