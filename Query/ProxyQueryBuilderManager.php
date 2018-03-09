<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Query;

use Artprima\QueryFilterBundle\Query\Condition\ConditionInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ProxyQueryBuilderManager
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query
 */
class ProxyQueryBuilderManager
{
    /**
     * @var ConditionInterface[]
     */
    private $conditions = [];

    public function registerCondition(ConditionInterface $condition)
    {
        $this->conditions[$condition->getName()] = $condition;
    }

    public function wrapQueryBuilder(QueryBuilder $queryBuilder)
    {
        $proxy = new ProxyQueryBuilder($queryBuilder);
        foreach ($this->conditions as $condition) {
            $proxy->registerCondition($condition);
        }

        return $proxy;
    }
}