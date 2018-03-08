<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Artprima\QueryFilterBundle\Exception\MissingArgumentException;
use Artprima\QueryFilterBundle\Query\Mysql\PaginationWalker;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * Class ProxyQueryBuilder
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class ProxyQueryBuilder
{
    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var bool
     */
    private $calcRows;

    public function __construct(QueryBuilder $queryBuilder, $calcRows = true)
    {
        $this->queryBuilder = $queryBuilder;
        $this->calcRows = $calcRows;
    }

    /**
     * @param int $i parameter id
     * @param string $field field name
     * @param string $condition condition type (eq, like, etc.)
     * @param array $val condition parameters information
     * @return DoctrineQuery\Expr\Comparison|DoctrineQuery\Expr\Func|string
     * @throws InvalidArgumentException
     */
    private function getConditionExpr(int $i, string $field, string $condition, array $val)
    {
        $value = $val['val'] ?? '';
        $qb = $this->queryBuilder;
        if ($condition === 'eq') {
            $expr = $qb->expr()->eq($field, '?'.$i);
            $qb->setParameter($i, $value);
        } elseif ($condition === 'not eq') {
            $expr = $qb->expr()->not($qb->expr()->eq($field, '?' . $i));
            $qb->setParameter($i, $value);
        } elseif ($condition === 'like') {
            $expr = $qb->expr()->like($field, '?'.$i);
            $qb->setParameter($i, '%'.$value.'%');
        } elseif ($condition === 'not like') {
            $expr = $qb->expr()->not($qb->expr()->like($field, '?'.$i));
            $qb->setParameter($i, '%'.$value.'%');
        } elseif ($condition === 'between') {
            $expr = $qb->expr()->between($field, ':x'.$i, ':y'.$i);
            $qb->setParameter('x'.$i, $val['x']);
            $qb->setParameter('y'.$i, $val['y']);
        } elseif ($condition === 'not between') {
            $expr = $qb->expr()->not($qb->expr()->between($field, ':x'.$i, ':y'.$i));
            $qb->setParameter('x'.$i, $val['x']);
            $qb->setParameter('y'.$i, $val['y']);
        } elseif ($condition === 'in') {
            $values = explode(',', $value);
            $values = array_map('trim', $values);
            $expr = $qb->expr()->in($field, '?'.$i);
            $qb->setParameter($i, $values);
        } elseif ($condition === 'not in') {
            $values = explode(',', $value);
            $values = array_map('trim', $values);
            $expr = $qb->expr()->notIn($field, '?' . $i);
            $qb->setParameter($i, $values);
        } elseif ($condition === 'is null') {
            $expr = $qb->expr()->isNull($field);
        } elseif ($condition === 'is not null') {
            $expr = $qb->expr()->isNotNull($field);
        } elseif ($condition === 'member of') {
            $expr = new DoctrineQuery\Expr\Comparison('?' . $i, 'MEMBER OF', $field);
            $qb->setParameter($i, $value);
        } elseif ($condition === 'gte') {
            $expr = $qb->expr()->gte($field, '?' . $i);
            $qb->setParameter($i, $value);
        } elseif ($condition === 'gt') {
            $expr = $qb->expr()->gt($field, '?' . $i);
            $qb->setParameter($i, $value);
        } elseif ($condition === 'lte') {
            $expr = $qb->expr()->lte($field, '?' . $i);
            $qb->setParameter($i, $value);
        } elseif ($condition === 'lt') {
            $expr = $qb->expr()->lt($field, '?' . $i);
            $qb->setParameter($i, $value);
        } else {
            // it can be possible in future to extend to further expressions if needed, otherwise throw exception
            throw new InvalidArgumentException(sprintf('Unexpected where type ("%s")', $condition));
        }

        return $expr;
    }

    /**
     * Get neighbor (prev or next) record id for use in navigation
     *
     * @param int $id record id
     * @param boolean $prev if true - get prev id, otherwise - next id
     * @param DoctrineQuery\Expr|null $extraAndWhereCondition
     * @return int|null neighbor id or null if empty result
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNeighborRecordId(int $id, bool $prev, ?DoctrineQuery\Expr $extraAndWhereCondition = null): ?int
    {
        $sign = $prev ? '<' : '>';
        $order = $prev ? 'DESC' : 'ASC';
        $rootEntities = $this->queryBuilder->getRootEntities();

        if (count($rootEntities) >= 0) {
            throw new \RuntimeException('QueryBuilder must contain exactly one root entity');
        }

        $rootEntity = reset($rootEntities);
        $qb = new QueryBuilder($this->queryBuilder->getEntityManager());
        $qb
            ->select('c.id') // assuming that the entities index must be always called `id`
            ->from($rootEntity, 'c')
            ->where('c.id '.$sign.' :id')
            ->setParameter(':id', $id)
            ->orderBy('c.id', $order)
        ;

        if ($extraAndWhereCondition !== null) {
            $qb->andWhere($extraAndWhereCondition);
        }

        $query = $qb->getQuery();
        $query->setMaxResults(1);
        $result = $query->getOneOrNullResult();

        return $result;
    }

    /**
     * Get prev and next record ids for the given record id
     *
     * @param int $id record id
     * @return array prev and next records id in an array with 'prev' and 'next' keys. One or both items can be null in case of no records.
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNeighborRecordIds(int $id): array
    {
        $prev = $this->getNeighborRecordId($id, true);
        $next = $this->getNeighborRecordId($id, false);

        return compact('prev', 'next');
    }

    /**
     * Get connector expression based on `and`, `or` or `null`
     *
     * @param $prev
     * @param $connector
     * @param $condition
     * @return DoctrineQuery\Expr\Andx|DoctrineQuery\Expr\Orx
     * @throws InvalidArgumentException
     */
    private function getConnectorExpr($prev, $connector, $condition)
    {
        $qb = $this->queryBuilder;

        if ($prev === null) {
            $expr = $condition;
        } elseif ($connector === null || $connector === 'and') {
            $expr = $qb->expr()->andX($prev, $condition);
        } elseif ($connector === 'or') {
            $expr = $qb->expr()->orX($prev, $condition);
        } else {
            throw new InvalidArgumentException(sprintf('Wrong connector type: %s', $connector));
        }

        return $expr;
    }

    /**
     * Add filter and order by conditions to the given QueryBuilder
     *
     * Example data
     *
     * array(
     *  'searchBy' => array(
     *    'e.name' => array(
     *      'type' => 'like',
     *      'val' => 'a',
     *    ),
     *    'e.city' => array(
     *      'type' => 'like',
     *      'val' => 'd',
     *    ),
     *    'c.name' => array(
     *      'type' => 'like',
     *      'val' => 'a',
     *    ),
     *    'concat(concat(concat(concat(p.firstname, ' '), p.middlename), ' '), p.lastname)' => array(
     *      'having' => TRUE
     *      'type' => 'like'
     *      'val' => 'a'
     *    )
     *    'year' => array(
     *      'type' => 'between',
     *      'val' => 2015,
     *      'x' => 'YEAR(e.startDate)',
     *      'y' => 'YEAR(e.endDate)'
     *    ),
     *  ),
     *  'sortData' => array(
     *      'e.name' => 'asc'
     *  )
     * )
     *
     * @param array $by
     * @param array $orderBy
     *
     * @throws MissingArgumentException
     * @return DoctrineQuery
     * @throws InvalidArgumentException
     */
    public function getSortedAndFilteredQuery(array $by, array $orderBy): DoctrineQuery
    {
        $qb = $this->queryBuilder;

        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $dir) {
                $qb->addOrderBy($field, strtoupper($dir));
            }
        }
        if (!empty($by)) {
            $i = 0;
            $where = null;
            $having = null;
            foreach ($by as $key => $val) {
                $i++;
                if (is_scalar($val)) {
                    $where = $this->getConnectorExpr($where, 'and', $qb->expr()->eq($key, '?'.$i));
                    $qb->setParameter($i, $val);
                    // @todo: the following smells bad
                    //} elseif (is_callable($val)){
                    //    call_user_func_array($val, array(&$where, &$having, &$qb));
                } elseif (is_array($val)) {
                    if (!array_key_exists('x', $val) && !array_key_exists('y', $val)) {
                        if (!array_key_exists('val', $val)) {
                            throw new MissingArgumentException('Required "val" argument not given');
                        }
                        if (!is_scalar($val['val'])) {
                            throw new InvalidArgumentException(sprintf('Unexpected val php type ("%s")', gettype($val['val'])));
                        }
                    }

                    $condition = $this->getConditionExpr($i, $key, $val['type'], $val);

                    if (empty($val['having'])) {
                        $where = $this->getConnectorExpr($where, $val['connector'] ?? 'and', $condition);
                    } else {
                        $having = $this->getConnectorExpr($having, $val['connector'] ?? 'and', $condition);
                    }
                } else {
                    throw new InvalidArgumentException(sprintf('Unexpected val php type ("%s")', gettype($val)));
                }
            }
            if ($where) {
                $qb->add('where', $where);
            }
            if ($having) {
                $qb->add('having', $having);
            }
        }

        $query = $qb->getQuery();

        if ($this->calcRows) {
            $query->setHint(DoctrineQuery::HINT_CUSTOM_OUTPUT_WALKER, PaginationWalker::class);
            $query->setHint('mysqlWalker.sqlCalcFoundRows', true);
        }

        return $query;
    }
}
