<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Doctrine\ORM\QueryBuilder;

/**
 * Class NotBetween
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
class NotBetween implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, string $field, int $index, array $val)
    {
        $expr = $field . ' NOT BETWEEN ' . ':x'.$index . ' AND ' . ':y'.$index;
        $qb->setParameter('x'.$index, $val['x'] ?? '');
        $qb->setParameter('y'.$index, $val['y'] ?? '');

        return $expr;
    }

    public function getName(): string
    {
        return 'not between';
    }
}