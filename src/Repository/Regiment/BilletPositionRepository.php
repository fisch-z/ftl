<?php

declare(strict_types=1);

namespace App\Repository\Regiment;

use App\Entity\Regiment\BilletPosition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BilletPosition>
 */
class BilletPositionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BilletPosition::class);
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
