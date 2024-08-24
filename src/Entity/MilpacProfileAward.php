<?php

declare(strict_types=1);

namespace App\Entity;

readonly class MilpacProfileAward
{

    public function __construct(
        private string $category,
        private string $name,
        private array  $meta,
        // private int    $stackingCount,
        // private bool   $withValour,
        private array  $rows,
        private array  $extraRows = [],
    )
    {
    }


    public function getCategory(): string
    {
        return $this->category;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRows(): array
    {
        return array_values($this->rows);
    }

    public function getExtraRows(): array
    {
        return array_values($this->extraRows);
    }

    public function getAllRows(): array
    {
        return array_merge($this->getRows(), $this->extraRows);
    }

    public function getStackingType(): ?string
    {
        return $this->meta["stackingType"] ?: null;
    }

    public function getStackingMaxCount(): ?int
    {
        return $this->meta["stackingMaxCount"] ?? match ($this->getStackingType()) {
            // "Silver Star" => "",
            "Numeral" => 6,
            "Leaf" => $this->isWithValor() ? 15 : 20,
            "LeafAO" => 7,
            "Star" => 11,
            "Star100" => 100,
            // "Star100" => 101,
            "StarSilver" => 5,
            "Knot" => 10,
            "KnotNoStars" => 4,
            null => 0,
        };
    }

    public function getStackingCount(): int
    {
        return in_array($this->meta["awardGroup"] ?? "", ["High Altitude Low Opening Badge", "Army Parachutist Badge"]) ? count($this->extraRows) + 1 : count($this->rows);
    }

    public function isWithValor(): bool
    {
        return array_reduce($this->rows, fn($total, $row) => $total || $row["withValor"], false);
    }

    public function getImageFileName(): string
    {
        if (in_array($this->getCategory(), ["primarySpecialSkills", "secondarySpecialSkills", "awardedBadgesStackable"])) {
            $count = min($this->getStackingCount(), $this->getStackingMaxCount());
            return sprintf("appAssets/uniform/%s/%s-%d.png", $this->getCategory(), $this->getName(), $count);
        }
        return sprintf("appAssets/uniform/%s/%s.png", $this->getCategory(), $this->getName());
    }

    public function getStackingOverlayFileName(): ?string
    {
        $count = min($this->getStackingCount(), $this->getStackingMaxCount());
        $type = $this->isWithValor() ? "LeafWithValor" : $this->getStackingType();
        return $count > 1 || $this->isWithValor() ? sprintf("appAssets/uniform/stackingOverlays/%s/%s.png", $type, $count) : null;
    }
}
