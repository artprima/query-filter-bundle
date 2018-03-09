<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Query;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * Class NeighborRecords
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query
 */
class NeighborRecords
{
    /**
     * @var string
     */
    private $primaryKeyColumn;

    /**
     * @var string
     */
    private $rootEntity;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(string $from, EntityManager $entityManager, string $primaryKeyColumn = 'c.id')
    {
        $this->rootEntity = $from;
        $this->entityManager = $entityManager;
        $this->primaryKeyColumn = $primaryKeyColumn;
    }

    /**
     * Get neighbor (prev or next) record id for use in navigation
     *
     * @param int $id record id
     * @param boolean $prev if true - get prev id, otherwise - next id
     * @return QueryBuilder
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
