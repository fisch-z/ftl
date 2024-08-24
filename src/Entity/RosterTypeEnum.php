<?php

declare(strict_types=1);

namespace App\Entity;

enum RosterTypeEnum: string
{
    case COMBAT = "ROSTER_TYPE_COMBAT";
    case RESERVE = "ROSTER_TYPE_RESERVE";
    case ELOA = "ROSTER_TYPE_ELOA";
    case WALL_OF_HONOR = "ROSTER_TYPE_WALL_OF_HONOR";
    case ARLINGTON = "ROSTER_TYPE_ARLINGTON";
    case PAST_MEMBERS = "ROSTER_TYPE_PAST_MEMBERS";
}
