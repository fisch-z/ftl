<?php

namespace App\Repository\Regiment;

use App\Entity\Regiment\SectionPractice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SectionPractice>
 */
class SectionPracticeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SectionPractice::class);
    }

    public static function update_query_builder(QueryBuilder $queryBuilder, string $alias)
    {
        $aliases = $queryBuilder->getAllAliases();
        foreach ([
                     "$alias.platoon" => "platoon",
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
            ->addOrderBy("$alias.sort", "ASC")
            ->addOrderBy("$alias.title", "ASC");
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return self::update_query_builder(parent::createQueryBuilder($alias, $indexBy), $alias);
    }
}
