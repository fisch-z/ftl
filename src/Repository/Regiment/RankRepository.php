<?php

declare(strict_types=1);

namespace App\Repository\Regiment;

use App\Entity\Regiment\Rank;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rank>
 */
class RankRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rank::class);
    }

    public static function update_query_builder(QueryBuilder $queryBuilder, string $alias)
    {
        return $queryBuilder
            ->orderBy("$alias.sort", "ASC");
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return self::update_query_builder(parent::createQueryBuilder($alias, $indexBy), $alias);
    }
}
