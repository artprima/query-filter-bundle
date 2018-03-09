<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Doctrine\ORM\QueryBuilder;

/**
 * Class NotLike
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
class NotLike implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, string $field, int $index, array $val)
    {
        $expr = $qb->expr()->notLike($field, '?'.$index);

        $search = trim($val['val'] ?? '');

        if (empty($val['exact'])) {
            $words = preg_split('/[\s\.,]+/', $search);
            $search = $words ? implode('%', $words) : $search;
        }

        $qb->setParameter($index, '%'.$search.'%');

        return $expr;
    }

    public function getName(): string
    {
        return 'not like';
    }
}