<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * Class NeighborRecords.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class NeighborRecords
{
    public function __construct(private string $rootEntity, private EntityManager $entityManager, private string $primaryKeyColumn = 'c.id')
    {
    }

    /**
     * Get neighbor (prev or next) record id for use in navigation.
     *
     * @param int  $id   record id
     * @param bool $prev if true - get prev id, otherwise - next id
     */
    public function getQueryBuilderFilteredByNeighborRecord(int $id, bool $prev): QueryBuilder
    {
        $sign = $prev ? '<' : '>';
        $order = $prev ? 'DESC' : 'ASC';

        $qb = new QueryBuilder($this->entityManager);
        $qb
            ->select($this->primaryKeyColumn)
            ->from($this->rootEntity, 'c')
            ->where($this->primaryKeyColumn.' '.$sign.' :id')
            ->setParameter(':id', $id)
            ->orderBy($this->primaryKeyColumn, $order)
        ;

        return $qb;
    }
}
