<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Artprima\QueryFilterBundle\Query\Condition\ConditionInterface;
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
     * @var ConditionManager
     */
    private $conditionManager;

    public function __construct(QueryBuilder $queryBuilder, ConditionManager $conditionManager)
    {
        $this->queryBuilder = $queryBuilder;
        $this->conditionManager = $conditionManager;
    }

    /**
     * @param int $index parameter id
     * @param Filter $filter
     * @return DoctrineQuery\Expr\Comparison|DoctrineQuery\Expr\Func|string
     */
    private function getConditionExpr(int $index, Filter $filter)
    {
        if (!$this->conditionManager->offsetExists($filter->getType())) {
            throw new InvalidArgumentException(sprintf('Condition "%s" is not registered', $filter->getType()));
        }

        $conditionManager = $this->conditionManager[$filter->getType()];
        if (!$conditionManager instanceof ConditionInterface) {
            throw new InvalidArgumentException(sprintf('Condition "%s" is of invalid type "%s"', $filter->getType(), gettype($this->conditionManager[$filter->getType()])));
        }

        $expr = $conditionManager->getExpr($this->queryBuilder, $index, $filter);

        return $expr;
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

    private function addQueryFilters(QueryBuilder $qb, array $filterBy): QueryBuilder
    {
        if (empty($filterBy)) {
            return $qb;
        }

        $i = 0;
        $where = null;
        $having = null;

        /** @var Filter $val */
        foreach ($filterBy as $val) {
            if (!($val instanceof Filter)) {
                throw new InvalidArgumentException(sprintf('Unexpected val php type ("%s")', gettype($val)));
            }

            $i++;

            $condition = $this->getConditionExpr($i, $val);

            if ($val->isHaving()) {
                $having = $this->getConnectorExpr($having, $val->getConnector() ?? 'and', $condition);
            } else {
                $where = $this->getConnectorExpr($where, $val->getConnector() ?? 'and', $condition);
            }
        }

        if ($where) {
            $qb->add('where', $where);
        }

        if ($having) {
            $qb->add('having', $having);
        }

        return $qb;
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
     * @param array $filterBy
     * @param array $orderBy
     *
     * @return QueryBuilder
     */
    public function getSortedAndFilteredQueryBuilder(array $filterBy, array $orderBy): QueryBuilder
    {
        foreach ($orderBy as $field => $dir) {
            $this->queryBuilder->addOrderBy($field, strtoupper($dir));
        }

        return $this->addQueryFilters($this->queryBuilder, $filterBy);
    }
}
