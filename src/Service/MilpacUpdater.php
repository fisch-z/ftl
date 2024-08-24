<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MilpacProfile;
use App\Entity\Regiment\BilletAssignment;
use App\Entity\RosterTypeEnum;
use App\Repository\MilpacProfileRepository;
use App\Repository\Regiment\BilletAssignmentRepository;
use App\Repository\Regiment\BilletPositionRepository;
use App\Repository\Regiment\RankRepository;
use App\Repository\Regiment\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

// #[AsCronTask("*/5 * * * *", method: 'updateByRosterType', arguments: ['type' => 'ROSTER_TYPE_COMBAT'])]
// #[AsCronTask("10 4 * * *", method: 'updateByRosterType', arguments: ['type' => 'ROSTER_TYPE_RESERVE'])]
// #[AsCronTask("10 5 * * *", method: 'updateByRosterType', arguments: ['type' => 'ROSTER_TYPE_ELOA'])]
// #[AsCronTask("10 6 * * *", method: 'updateByRosterType', arguments: ['type' => 'ROSTER_TYPE_WALL_OF_HONOR'])]
// #[AsCronTask("10 7 * * *", method: 'updateByRosterType', arguments: ['type' => 'ROSTER_TYPE_ARLINGTON'])]
// #[AsCronTask("10 8 * * *", method: 'updateByRosterType', arguments: ['type' => 'ROSTER_TYPE_PAST_MEMBERS'])]
#[AsCronTask("*/10 * * * *", method: 'updateAll')]
class MilpacUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        #[Autowire(service: "milpacApiHttpClient")]
        private HttpClientInterface        $httpClient,
        private CacheInterface             $cache,
        private MilpacProfileRepository    $milpacProfileRepository,
        LoggerInterface                    $milpacUpdatesLogger,
        private EntityManagerInterface     $entityManager,
        private BilletAssignmentRepository $billetAssignmentRepository,
        private BilletPositionRepository   $billetPositionRepository,
        private SectionRepository          $sectionRepository,
        private RankRepository             $rankRepository,
    )
    {
        $this->logger = $milpacUpdatesLogger;
    }

    public function updateAll()
    {
        foreach ([
                     RosterTypeEnum::COMBAT,
                     RosterTypeEnum::RESERVE,
                     RosterTypeEnum::ELOA,
                     RosterTypeEnum::WALL_OF_HONOR,
                     RosterTypeEnum::ARLINGTON,
                     RosterTypeEnum::PAST_MEMBERS,
                 ] as $rosterType) {
            $this->updateByRosterType($rosterType);
        }
    }

    public function updateByRosterType(string $type): void
    {
        $this->logger->info("updating profiles for roster type $type");
        $milpacDataProfiles = $this->cache->get("milpac_updater_$type", function (CacheItemInterface $cacheItem) use ($type) {
            $cacheItem->expiresAfter(180); // 3 minutes
            $response = $this->httpClient->request("GET", "roster/{$type}");
            return $response->toArray()["profiles"];
        });
        foreach ($milpacDataProfiles as $milpacData) {
            $this->updateOrCreateEntity($milpacData);
        }
        $this->entityManager->flush();
        $this->logger->info(sprintf("finished updating $type with %d profiles", count($milpacDataProfiles)));
    }

    public function updateByKeycloakId(string $keycloakId): void
    {
        $this->logger->info("updating user by keycloakId $keycloakId");
        $milpacData = $this->cache->get("milpac_updater_byKeycloakId_$keycloakId", function (CacheItemInterface $cacheItem) use ($keycloakId) {
            $cacheItem->expiresAfter(180); // 3 minutes
            $response = $this->httpClient->request("GET", "milpac/keycloak/$keycloakId");
            return $response->toArray();
        });
        $this->updateOrCreateEntity($milpacData);
        $this->entityManager->flush();
    }

    protected function updateOrCreateEntity(array $milpacData): void
    {
        $milpacProfile = $this->milpacProfileRepository->findByUserId((int)$milpacData["user"]["userId"]);
        $milpacProfile = $milpacProfile ?: new MilpacProfile();
        $milpacProfile->setUserId((int)$milpacData["user"]["userId"]);
        $milpacProfile->setUsername($milpacData["user"]["username"]);
        $milpacProfile->setRosterType($milpacData["roster"]);
        $milpacProfile->setKeycloakId($milpacData["keycloakId"]);
        $milpacProfile->setJoinedAt(\DateTimeImmutable::createFromFormat("Y-m-d", $milpacData["joinDate"]));

        if (preg_match("@^https://7cav\.us/data/roster_uniforms/\d+/(\d+)\.\w+$@", $milpacData["uniformUrl"], $matches)) {
            $milpacProfile->setForumProfileId((int)$matches[1]);
        } else {
            throw new \Exception("Could not determine forumProfileId for {$milpacProfile->getUsername()}");
        }

        if (!$milpacProfile->getCreatedAt()) {
            $milpacProfile->setCreatedAt(new \DateTime());
        }
        $milpacProfile->setUpdatedAt(new \DateTime());
        $milpacProfile->setSyncedAt(new \DateTimeImmutable());

        $newRankId = (int)($milpacData["rank"]["rankId"] ?? 0);
        $rank = $milpacProfile->getRank();
        if ($newRankId !== $rank?->getId()) {
            $rank = $this->rankRepository->findOneBy(["id" => $newRankId]);
            $milpacProfile->setRank($rank);
        }

        $diff = [
            "current" => "",
            "new" => "",
        ];
        foreach ([
                     "current" => $milpacProfile->getData(),
                     "new" => $milpacData,
                 ] as $key => $data) {
            $_data = [
                $data["rank"]["rankId"] ?? "",
                $data["roster"] ?? "",
                $data["user"]["username"] ?? "",
                $data["primary"]["positionId"] ?? "",
            ];
            foreach ($data["secondaries"] ?? [] as $award) {
                $_data[] = $award["positionId"];
            }
            foreach ($data["awards"] ?? [] as $award) {
                $_data[] = $award["awardName"];
            }
            sort($_data);
            $diff[$key] .= implode("-", $_data);
        }
        if (!$milpacProfile->getMilpacDataChangeAt() || $diff["current"] !== $diff["new"]) {
            $milpacProfile->setMilpacDataChangeAt(new \DateTimeImmutable());
        }
        $milpacProfile->setData($milpacData);
        if ($milpacData["primary"]) {
            $milpacProfile->setPrimaryBilletAssignment(
                $this->findBilletAssignment((int)$milpacData["primary"]["positionId"], $milpacData["primary"]["positionTitle"])
            );
        }
        $milpacProfile->removeAllBilletAssignments();
        foreach ([$milpacData["primary"], ...$milpacData["secondaries"]] as $billetData) {
            $milpacProfile->addBilletAssignment(
                $this->findBilletAssignment((int)$billetData["positionId"], $billetData["positionTitle"])
            );
        }
        $this->entityManager->persist($milpacProfile);
    }

    private function findBilletAssignment(int $milpacId, string $milpacTitle)
    {
        $obj = $this->billetAssignmentRepository->findOneBy(["milpacId" => $milpacId]);
        if (!$obj) {
            $billetAssignment = new BilletAssignment();
            $billetAssignment->setMilpacId((int)$milpacId);
            $billetAssignment->setMilpacTitle((string)$milpacTitle);
            $billetAssignment->setPosition($this->billetPositionRepository->findOneBy(["id" => 24]));
            $billetAssignment->setSection($this->sectionRepository->findOneBy(["id" => 305]));
            $this->entityManager->persist($billetAssignment);
            $this->entityManager->flush();
        }
        return $obj;
    }
}
