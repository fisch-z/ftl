<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MilpacProfile;
use App\Repository\Regiment\BilletAssignmentRepository;
use App\Repository\Regiment\BilletPositionRepository;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Cache\CacheInterface;

class MilpacUniformRenderer
{
    private string $basePath;
    private string $uniformAssetsPath;

    public function __construct(
        KernelInterface                             $appKernel,
        private readonly ImageManager               $imageManager,
        private readonly CacheInterface             $cache,
        private readonly BilletAssignmentRepository $billetAssignmentRepository,
        private readonly BilletPositionRepository   $billetPositionRepository,
    )
    {
        $this->basePath = $appKernel->getProjectDir() . '/public';
        $this->uniformAssetsPath = "{$this->basePath}/appAssets/uniform";
    }

    public function render(MilpacProfile $milpacProfile)
    {
        return new MilpacUniformImage(
            $milpacProfile,
            $this->imageManager,
            $this->basePath,
            $this->uniformAssetsPath,
            $this->billetAssignmentRepository,
            $this->billetPositionRepository,
            $this->cache,
        );
    }
}
