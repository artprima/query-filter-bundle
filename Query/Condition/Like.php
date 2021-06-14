<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;
use Stringable;

/**
 * Class Like.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class Like implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter): string|Stringable
    {
        $expr = $qb->expr()->like($filter->getField(), '?'.$index);

        $search = trim($filter->getX() ?? '');

        if ('exact' !== $filter->getExtra()) {
            $words = preg_split('/[\s\.,]+/', $search);
            $search = $words ? implode('%', $words) : $search;
        }

        $qb->setParameter($index, '%'.$search.'%');

        return $expr;
    }
}
