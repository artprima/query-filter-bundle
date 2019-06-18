<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;

/**
 * Interface ConditionInterface
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
interface ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter);
}