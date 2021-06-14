<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;
use Stringable;

/**
 * Class Gte.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class Gte implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter): string|Stringable
    {
        $expr = $qb->expr()->gte($filter->getField(), '?'.$index);
        $qb->setParameter($index, $filter->getX() ?? '');

        return $expr;
    }
}
