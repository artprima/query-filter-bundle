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
        $expr = $qb->expr()->isMemberOf('?' . $index, $field);
        $values = explode(',', $val['val'] ?? '');
        $values = array_map('trim', $values);
        $qb->setParameter($index, $values);

        return $expr;
    }

    public function getName(): string
    {
        return 'member of';
    }
}