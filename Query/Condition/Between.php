<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Between
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class Between implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter)
    {
        $expr = $qb->expr()->between($filter->getField(), ':x'.$index, ':y'.$index);
        $qb->setParameter('x'.$index, $filter->getX() ?? '');
        $qb->setParameter('y'.$index, $filter->getY() ?? '');

        return $expr;
    }
}