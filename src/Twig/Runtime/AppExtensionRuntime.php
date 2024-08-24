<?php

declare(strict_types=1);

namespace App\Twig\Runtime;

use App\Service\NavigationGenerator;
use Twig\Extension\RuntimeExtensionInterface;

readonly class AppExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private NavigationGenerator $navigationGenerator,
    )
    {
    }

    public function getOrganisation()
    {
        return [
            "title" => "7Cav",
            "titleFull" => "7th Cavalry Gaming",
        ];
    }

    public function getMainNavigation(): array
    {
        return $this->navigationGenerator->generateNavigationItems([
            [
                "title" => "FTL",
                "route" => "app_home_index",
                "match_route" => "app_home_",
            ],
            // [
            //     "title" => "AAR",
            //     "route" => "app_roster_index",
            //     "match_route" => "app_roster_",
            // ],
            // [
            //     "title" => "ACE",
            //     "route" => "app_accountability_index",
            //     "match_route" => "app_accountability_",
            // ],
            [
                "title" => "SQD",
                "route" => "app_regimentaldata_index",
                "match_route" => "app_regimentaldata_",
            ],
            // [
            //     "title" => "Milpac",
            //     "route" => "app_milpac_index",
            //     "match_route" => "app_milpac_",
            // ],
            [
                "title" => "UAV",
                "route" => "app_uniform_index",
                "match_route" => "app_uniform_",
            ],
            [
                "title" => "Admin",
                "route" => "admin",
            ],
        ]);

    }
}
