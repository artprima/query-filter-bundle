<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;
use Stringable;

/**
 * Class NotBetween.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class NotBetween implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter): string|Stringable
    {
        $expr = $filter->getField().' NOT BETWEEN '.':x'.$index.' AND '.':y'.$index;
        $qb->setParameter('x'.$index, $filter->getX() ?? '');
        $qb->setParameter('y'.$index, $filter->getY() ?? '');

        return $expr;
    }
}
