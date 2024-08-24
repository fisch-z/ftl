<?php

declare(strict_types=1);

namespace App\Repository\Regiment;

use App\Entity\Regiment\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public static function update_query_builder(QueryBuilder $queryBuilder, string $alias)
    {
        $aliases = $queryBuilder->getAllAliases();
        foreach ([
                     "$alias.battalion" => "battalion",
                 ] as $join => $_alias) {
            if (!in_array($_alias, $aliases)) {
                $queryBuilder = $queryBuilder->leftJoin($join, $_alias);
            }
        }
        return $queryBuilder
            ->orderBy("battalion.sort", "ASC")
            ->addOrderBy("battalion.title", "ASC")
            ->addOrderBy("$alias.sort", "ASC")
            ->addOrderBy("$alias.title", "ASC");
    }

    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return self::update_query_builder(parent::createQueryBuilder($alias, $indexBy), $alias);
    }
}
