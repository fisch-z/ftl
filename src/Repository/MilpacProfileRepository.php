<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MilpacProfile;
use App\Entity\RosterTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MilpacProfile>
 */
class MilpacProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MilpacProfile::class);
    }

    public static function update_query_builder(QueryBuilder $queryBuilder, string $alias)
    {
        $aliases = $queryBuilder->getAllAliases();
        foreach ([
                     "$alias.rank" => "rank",
                     "$alias.primaryBilletAssignment" => "primaryBilletAssignment",
                     "primaryBilletAssignment.position" => "position",
                     "primaryBilletAssignment.section" => "section",
                     "section.platoon" => "platoon",
                     "platoon.company" => "company",
                     "company.battalion" => "battalion",
                 ] as $join => $_alias) {
            if (!in_array($_alias, $aliases)) {
                $queryBuilder = $queryBuilder->leftJoin($join, $_alias);
            }
        }
        return $queryBuilder
            ->orderBy("battalion.sort", "ASC")
            ->addOrderBy("battalion.title", "ASC")
            ->addOrderBy("company.sort", "ASC")
            ->addOrderBy("company.title", "ASC")
            ->addOrderBy("platoon.sort", "ASC")
            ->addOrderBy("platoon.title", "ASC")
            ->addOrderBy("section.sort", "ASC")
            ->addOrderBy("section.title", "ASC")
            ->addOrderBy("position.sort", "ASC")
            ->addOrderBy("rank.sort", "ASC")
            ->addOrderBy("$alias.joinedAt", "ASC");
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return self::update_query_builder(parent::createQueryBuilder($alias, $indexBy), $alias);
    }

    public function findByUserId(int $value): ?MilpacProfile
    {
        return $this->createQueryBuilder('milpacProfile')
            ->andWhere('milpacProfile.userId = :val')
            ->setParameter('val', $value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByRosterType(RosterTypeEnum $value): array
    {
        return $this->createQueryBuilder('milpacProfile')
            ->andWhere('milpacProfile.rosterType = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult();
    }
}
