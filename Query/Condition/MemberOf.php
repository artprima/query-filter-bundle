<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * Class MemberOf
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
class MemberOf implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, string $field, int $index, array $val)
    {
        $expr = new DoctrineQuery\Expr\Comparison('?' . $index, 'MEMBER OF', $field);
        $qb->setParameter($index, $val['val'] ?? '');

        return $expr;
    }

    public function getName(): string
    {
        return 'member of';
    }
}