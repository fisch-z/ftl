<?php

declare(strict_types=1);

namespace App\Service;

use App\Utility\OrganisationalWeek;

readonly class OrganisationalWeekService
{
    public function __construct()
    {
    }

    public function getCurrentWeek(): OrganisationalWeek
    {
        return $this->getWeekForDate(new \DateTimeImmutable());
    }

    public function getWeekForDate(\DateTimeImmutable $date): OrganisationalWeek
    {
        return new OrganisationalWeek($date);
    }
}
