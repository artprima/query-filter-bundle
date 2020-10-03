<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;

/**
 * Class NotLike
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class NotLike implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter)
    {
        $expr = $qb->expr()->notLike($filter->getField(), '?'.$index);

        $search = trim($filter->getX() ?? '');

        if ($filter->getExtra() !== 'exact') {
            $words = preg_split('/[\s\.,]+/', $search);
            $search = $words ? implode('%', $words) : $search;
        }

        $qb->setParameter($index, '%'.$search.'%');

        return $expr;
    }
}