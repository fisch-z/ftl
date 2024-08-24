<?php

namespace App\Entity\Regiment;

use App\Repository\Regiment\SectionPracticeRepository;
use App\Utility\OrganisationalWeek;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectionPracticeRepository::class)]
class SectionPractice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sectionPractices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Section $section = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateTime = null;

    #[ORM\Column]
    private array $attendance = [];

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateTime(): ?\DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function setDateTime(\DateTimeImmutable $dateTime): static
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function getAttendance(): array
    {
        return $this->attendance;
    }

    public function setAttendance(array $attendance): static
    {
        $this->attendance = $attendance;

        return $this;
    }

    public function getWeek(): OrganisationalWeek
    {
        return new OrganisationalWeek($this->getDateTime());
    }

    public function isToday(): bool
    {
        return $this->getDateTime()->format("Y-m-d") === (new \DateTimeImmutable())->format("Y-m-d");
    }

    public function isInPast(): bool
    {
        return $this->getDateTime() < new \DateTimeImmutable();
    }

    public function isInCurrentWeek(): bool
    {
        $now = new \DateTimeImmutable();
        $week = $this->getWeek();
        return $now >= $week->getFirstDay() && $now <= $week->getLastDay();
    }

    public function getAttendanceList()
    {
        foreach ($this->getAttendance() as $row) {
            yield [
                "link" => ($row["rosterId"] ?? null) ? "https://7cav.us/rosters/profile/{$row["rosterId"]}/" : "",
                ...$row,
            ];
        }
    }
}
