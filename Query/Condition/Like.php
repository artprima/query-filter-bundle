<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Like
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
class Like implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter)
    {
        $expr = $qb->expr()->like($filter->getField(), '?'.$index);

        $search = trim($filter->getX() ?? '');

        if ($filter->getExtra() !== 'exact') {
            $words = preg_split('/[\s\.,]+/', $search);
            $search = $words ? implode('%', $words) : $search;
        }

        $qb->setParameter($index, '%'.$search.'%');

        return $expr;
    }
}