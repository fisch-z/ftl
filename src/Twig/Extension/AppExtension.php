<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    // public function getFilters(): array
    // {
    //     return [
    //         // If your filter generates SAFE HTML, you should add a third
    //         // parameter: ['is_safe' => ['html']]
    //         // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
    //         new TwigFilter('filter_name', [AppExtensionRuntime::class, 'testFilter']),
    //     ];
    // }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('organisation', [AppExtensionRuntime::class, 'getOrganisation']),
            new TwigFunction('mainNavigation', [AppExtensionRuntime::class, 'getMainNavigation']),
        ];
    }
}
