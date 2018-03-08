<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Doctrine\ORM\QueryBuilder;

/**
 * Interface ConditionInterface
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
interface ConditionInterface
{
    public function getExpr(QueryBuilder $qb, string $field, int $index, array $val);
    public function getName(): string;
}