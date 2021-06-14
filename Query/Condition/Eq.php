<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Eq.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class Eq implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter): string
    {
        $expr = $qb->expr()->eq($filter->getField(), '?'.$index);
        $qb->setParameter($index, $filter->getX() ?? '');

        return $expr;
    }
}
