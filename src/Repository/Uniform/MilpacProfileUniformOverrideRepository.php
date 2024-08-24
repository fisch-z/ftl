<?php

declare(strict_types=1);

namespace App\Repository\Uniform;

use App\Entity\Uniform\MilpacProfileUniformOverride;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MilpacProfileUniformOverride>
 */
class MilpacProfileUniformOverrideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MilpacProfileUniformOverride::class);
    }

    public static function update_query_builder(QueryBuilder $queryBuilder, string $alias)
    {
        return $queryBuilder;
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return self::update_query_builder(parent::createQueryBuilder($alias, $indexBy), $alias);
    }
}
