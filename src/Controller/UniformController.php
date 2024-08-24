<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MilpacProfile;
use App\Entity\RosterTypeEnum;
use App\Repository\MilpacProfileRepository;
use App\Service\MilpacUniformRenderer;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route("/uav")]
class UniformController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MilpacProfileRepository $milpacRepository,
        private HttpClientInterface              $milpacApiHttpClient,
        private CacheInterface                   $cache,
        private ImageManager                     $imageManager,
        private KernelInterface                  $appKernel,
        private EntityManagerInterface           $entityManager,
    )
    {
    }

    #[Route("/", name: "app_uniform_index", methods: ["GET"])]
    public function index(): Response
    {
        /** @var MilpacProfile[] $profiles */
        $profiles = $this->milpacRepository->findByRosterType(RosterTypeEnum::COMBAT);
        $updateRequiredCount = 0;
        foreach ($profiles as $profile) {
            if (!in_array($profile->getChangeStatus(), ["updated", "not-tracked"])) {
                $updateRequiredCount++;
            }
        }
        $html = $this->renderView("uniform/index.html.twig", [
            "milpacProfiles" => $profiles,
            "milpacProfilesUpdateRequiredCount" => $updateRequiredCount,
        ]);
        return new Response($html);
    }

    #[Route("/{userId<\d+>}", name: "app_uniform_show", methods: ["GET"])]
    public function show(MilpacProfile $milpacProfile): Response
    {
        if (!$milpacProfile) {
            throw $this->createNotFoundException("Milpac not found");
        }
        return $this->render("uniform/show.html.twig", [
            "milpacProfile" => $milpacProfile,
        ]);
    }

    #[Route("/{userId<\d+>}/image", name: "app_uniform_image", methods: ["GET"])]
    public function image(MilpacUniformRenderer $milpacUniformRenderer, MilpacProfile $milpacProfile = null): Response
    {
        if (!$milpacProfile) {
            throw $this->createNotFoundException("Milpac not found");
        }
        $image = $milpacUniformRenderer->render($milpacProfile);
        $_image = $image->toPng();
        $response = new Response();
        $response->headers->set("Content-Type", $_image->mimetype());
        $response->setContent($_image->toString());
        return $response;
    }

    #[Route("/{userId<\d+>}/markupdated", name: "app_uniform_markupdated", methods: ["GET"])]
    public function markAsUpdated(MilpacProfile $milpacProfile)
    {
        $milpacProfile->setUniformReplacedAt(new \DateTimeImmutable());
        $this->entityManager->persist($milpacProfile);
        $this->entityManager->flush();
        return $this->redirectToRoute("app_uniform_show", ["userId" => $milpacProfile->getUserId()]);
    }

}
