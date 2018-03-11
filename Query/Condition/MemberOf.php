<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MemberOf
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
class MemberOf implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter)
    {
        $expr = $qb->expr()->isMemberOf('?'.$index, $filter->getField());
        $values = explode(',', $filter->getX() ?? '');
        $values = array_map('trim', $values);
        $qb->setParameter($index, $values);

        return $expr;
    }
}