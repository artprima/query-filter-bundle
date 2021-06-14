<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;
use Stringable;

/**
 * Class MemberOf.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class MemberOf implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter): string|Stringable
    {
        $expr = $qb->expr()->isMemberOf('?'.$index, $filter->getField());
        $values = explode(',', $filter->getX() ?? '');
        $values = array_map('trim', $values);
        $qb->setParameter($index, $values);

        return $expr;
    }
}
