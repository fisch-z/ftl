<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Regiment\BilletAssignment;
use App\Entity\Regiment\Rank;
use App\Entity\Regiment\ServiceBranch;
use App\Entity\Uniform\MilpacProfileUniformOverride;
use App\Repository\MilpacProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: MilpacProfileRepository::class)]
class MilpacProfile
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?int $userId = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $rosterType = null;

    #[ORM\Column]
    private array $data = [];

    private ?array $_serviceRecords = null;

    public function __toString(): string
    {
        return $this->getRankShort() . " " . $this->getUsername();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getUserNameWithRank()
    {
        return "{$this->getRankShort()}.{$this->getUsername()}";
    }

    public function getRosterType(): ?string
    {
        return $this->rosterType;
    }

    public function setRosterType(string $rosterType): static
    {
        $this->rosterType = $rosterType;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getDataJson(): string
    {
        return json_encode($this->getData(), JSON_PRETTY_PRINT);
    }

    public function getServiceRecords(): array
    {
        if ($this->_serviceRecords === null) {
            $this->_serviceRecords = $this->getData()["records"];
            usort($this->_serviceRecords, fn($a, $b) => strtotime($a["recordDate"]) <=> strtotime($b["recordDate"]));
            $this->_serviceRecords = array_reverse($this->_serviceRecords);
            foreach ($this->_serviceRecords as &$record) {
                $record["recordDetails"] = trim($record["recordDetails"]);
            }
        }
        return $this->_serviceRecords;
    }

    public function getChangeStatus()
    {
        // if (in_array($this->getUsername(), ["Jarvis.A"])) {
        //     return "manual-update-required";
        // }
        if (!$this->getUniformReplacedAt() || $this->getUniformReplacedAt() < $this->getMilpacDataChangeAt()) {
            if ($this->getPrimaryBilletAssignment()->getPosition()->getTitle() === "New Recruit") {
                return "not-tracked";
            }
            return "update-required";
        }
        return "updated";
    }


    public function getChangeStatusText()
    {
        return match ($this->getChangeStatus()) {
            // "manual-update-required" => "manual update required",
            "update-required" => "update required",
            "updated" => "updated",
            "not-tracked" => "Not tracked",
        };
    }

    public function getServiceBranch(): ServiceBranch
    {
        $override = $this->getMilpacProfileUniformOverride();
        if ($override && $override->getServiceBranch()) {
            return $override->getServiceBranch();
        }
        return $this->getPrimaryBilletAssignment()->getServiceBranch();
    }

    protected ?MilpacProfileAwardList $_awardList = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $syncedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $milpacDataChangeAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $uniformReplacedAt = null;

    /**
     * @var Collection<int, BilletAssignment>
     */
    #[ORM\ManyToMany(targetEntity: BilletAssignment::class, inversedBy: 'milpacProfiles')]
    private Collection $billetAssignments;

    #[ORM\Column(length: 255)]
    private ?string $keycloakId = null;

    // TODO we should rename this to rosterId or similar because it is actually only used for roster and uniform. The userId is actually the id used for forum profiles
    #[ORM\Column]
    private ?int $forumProfileId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $joinedAt = null;

    #[ORM\ManyToOne(inversedBy: 'milpacProfilesWithPrimary')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BilletAssignment $primaryBilletAssignment = null;

    #[ORM\ManyToOne(inversedBy: 'milpacProfiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rank $rank = null;

    #[ORM\OneToOne(mappedBy: 'milpacProfile', cascade: ['persist', 'remove'])]
    private ?MilpacProfileUniformOverride $milpacProfileUniformOverride = null;

    public function __construct()
    {
        $this->billetAssignments = new ArrayCollection();
    }

    public function getAwardList(): MilpacProfileAwardList
    {
        if ($this->_awardList === null) {
            $this->_awardList = new MilpacProfileAwardList(
                $this->getData()['awards'],
                $this,
                []
            );
        }
        return $this->_awardList;
    }

    public function getRankShort(): string
    {
        return $this->getRank()->getTitleShort();
    }

    public function isAide(): bool
    {
        $positionTitle = $this->getPrimaryBilletAssignment()->getPosition()->getTitle();
        return str_starts_with($positionTitle, "Aide to the");
    }

    public function isOfficer(): bool
    {
        return $this->getRank()->getRankType() === "officer";
    }

    public function isNonCommissionedOfficer(): bool
    {
        return $this->getRank()->getRankType() === "nonCommissionedOfficer";
    }

    public function isTrooper(): bool
    {
        return $this->getRank()->getRankType() === "trooper";
    }

    public function isWarrantOfficer(): bool
    {
        return $this->getRank()->getRankType() === "warrantOfficer";
    }

    public function getSyncedAt(): ?\DateTimeImmutable
    {
        return $this->syncedAt;
    }

    public function setSyncedAt(?\DateTimeImmutable $syncedAt): static
    {
        $this->syncedAt = $syncedAt;

        return $this;
    }

    public function getMilpacDataChangeAt(): ?\DateTimeImmutable
    {
        return $this->milpacDataChangeAt;
    }

    public function setMilpacDataChangeAt(?\DateTimeImmutable $milpacDataChangeAt): static
    {
        $this->milpacDataChangeAt = $milpacDataChangeAt;

        return $this;
    }

    public function getUniformReplacedAt(): ?\DateTimeImmutable
    {
        return $this->uniformReplacedAt;
    }

    public function setUniformReplacedAt(?\DateTimeImmutable $uniformReplacedAt): static
    {
        $this->uniformReplacedAt = $uniformReplacedAt;

        return $this;
    }

    /**
     * @return Collection<int, BilletAssignment>
     */
    public function getBilletAssignmentsSorted(): Collection
    {
        $primary = $this->getPrimaryBilletAssignment();
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->neq("id", $primary->getId()))// ->orderBy(["position.sort" => "ASC"])
        ;
        $return = $this->getBilletAssignments()->matching($criteria)->toArray();
        usort($return, static function (BilletAssignment $a, BilletAssignment $b) {
            return $a->getPosition()->getSort() <=> $b->getPosition()->getSort();
        });
        return new ArrayCollection([$primary, ...$return]);
    }

    /**
     * @return Collection<int, BilletAssignment>
     */
    public function getBilletAssignments(): Collection
    {
        return $this->billetAssignments;
    }

    public function addBilletAssignment(BilletAssignment $billetAssignment): static
    {
        if (!$this->billetAssignments->contains($billetAssignment)) {
            $this->billetAssignments->add($billetAssignment);
        }

        return $this;
    }

    public function removeBilletAssignment(BilletAssignment $billetAssignment): static
    {
        $this->billetAssignments->removeElement($billetAssignment);

        return $this;
    }

    public function removeAllBilletAssignments(): static
    {
        $this->billetAssignments->clear();

        return $this;
    }


    public function getKeycloakId(): ?string
    {
        return $this->keycloakId;
    }

    public function setKeycloakId(string $keycloakId): static
    {
        $this->keycloakId = $keycloakId;

        return $this;
    }

    public function setForumProfileId(int $forumProfileId): static
    {
        $this->forumProfileId = $forumProfileId;

        return $this;
    }

    public function getForumProfileId(): int
    {
        return $this->forumProfileId;
    }

    public function getForumProfileLink(): string
    {
        return "https://7cav.us/rosters/profile/{$this->getForumProfileId()}/";
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeImmutable $joinedAt): static
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }

    public function getPrimaryBilletAssignment(): ?BilletAssignment
    {
        return $this->primaryBilletAssignment;
    }

    public function setPrimaryBilletAssignment(?BilletAssignment $primaryBilletAssignment): static
    {
        $this->primaryBilletAssignment = $primaryBilletAssignment;

        return $this;
    }

    public function getRank(): ?Rank
    {
        return $this->rank;
    }

    public function setRank(?Rank $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getMilpacProfileUniformOverride(): ?MilpacProfileUniformOverride
    {
        return $this->milpacProfileUniformOverride;
    }

    public function setMilpacProfileUniformOverride(MilpacProfileUniformOverride $milpacProfileUniformOverride): static
    {
        // set the owning side of the relation if necessary
        if ($milpacProfileUniformOverride->getMilpacProfile() !== $this) {
            $milpacProfileUniformOverride->setMilpacProfile($this);
        }

        $this->milpacProfileUniformOverride = $milpacProfileUniformOverride;

        return $this;
    }

    public function findOneServiceRecord($pattern, $recordType)
    {
        foreach ($this->getServiceRecords() as $row) {
            if ($row["recordType"] === $recordType) {
                if (preg_match($pattern, $row["recordDetails"])) {
                    return $row;
                }
            }
        }
        return null;
    }

    public function hasNcoa()
    {
        return !!$this->findOneServiceRecord("@^(Graduated|Complete) NCOA (Warrior Leadership Course|WLC)( Phase II|[\s,]*(|Class)[\s\d/\-\.\*]*)(|,? Named Honor Graduate)$@", "RECORD_TYPE_GRADUATION");
    }

    public function hasOds()
    {
        return !!$this->findOneServiceRecord("@^Graduated Officer Development School(|[\s,]*Class[\s\d/\-\.\*]*)$@", "RECORD_TYPE_GRADUATION");
    }

    public function hasSac()
    {
        return !!$this->findOneServiceRecord("@^Attended(| the) Server Admin(|istration) Course(|[\s,]*(|Class)[\s\d/\-\.\*]*)$@", "RECORD_TYPE_GRADUATION");
    }
}


