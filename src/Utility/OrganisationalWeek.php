<?php

declare(strict_types=1);

namespace App\Utility;

readonly class OrganisationalWeek
{
    private \DateTimeImmutable $firstDay;
    private \DateTimeImmutable $lastDay;

    public function __construct(
        private \DateTimeImmutable $date,
    )
    {
        $this->firstDay = $this->date->format("N") === "6" ? $this->date : $this->date->modify("last Saturday");
        $this->lastDay = $this->firstDay->modify("+6 days");
    }

    public function getFirstDay(): \DateTimeImmutable
    {
        return $this->firstDay;
    }

    public function getLastDay(): \DateTimeImmutable
    {
        return $this->lastDay;
    }

    public function getPreviousWeek(): OrganisationalWeek
    {
        return new self($this->firstDay->modify("-1 day"));
    }

    public function getNextWeek(): OrganisationalWeek
    {
        return new self($this->lastDay->modify("+1 day"));
    }

    public function __toString(): string
    {
        return strtoupper("{$this->getFirstDay()->format("dMy")} - {$this->getLastDay()->format("dMy")}");
    }
}
