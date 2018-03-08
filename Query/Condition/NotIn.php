<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Doctrine\ORM\QueryBuilder;

/**
 * Class NotIn
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
class NotIn implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, string $field, int $index, array $val)
    {
        $values = explode(',', $val['val'] ?? '');
        $values = array_map('trim', $values);
        $expr = $qb->expr()->notIn($field, '?' . $index);
        $qb->setParameter($index, $values);

        return $expr;
    }

    public function getName(): string
    {
        return 'not in';
    }
}