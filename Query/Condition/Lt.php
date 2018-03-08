<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Doctrine\ORM\QueryBuilder;

/**
 * Class Lt
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
class Lt implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, string $field, int $index, array $val)
    {
        $expr = $qb->expr()->lt($field, '?' . $index);
        $qb->setParameter($index, $val['val'] ?? '');

        return $expr;
    }

    public function getName(): string
    {
        return 'lt';
    }
}