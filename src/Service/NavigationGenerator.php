<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class NavigationGenerator
{
    public function __construct(
        private RequestStack          $requestStack,
        private UrlGeneratorInterface $urlGenerator,
    )
    {
    }

    public function generateNavigationItems($items): array
    {
        $request = $this->requestStack->getCurrentRequest();
        return array_map(function ($item) use ($request) {
            $matchRoutes = (array)($item["match_route"] ?? $item["route"]);
            $currentRoute = $request->get('_route');
            return [
                ...$item,
                "url" => $this->urlGenerator->generate($item["route"]),
                "active" => array_reduce($matchRoutes, function (bool $carry, string $matchRoute) use ($currentRoute) {
                    return $carry || str_starts_with($currentRoute, $matchRoute);
                }, false),
            ];
        }, $items);
    }
}
