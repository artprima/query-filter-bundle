<?php

/*
 * This file is part of the QueryFilterBundle package.
 *
 * (c) Denis Voytyuk <ask@artprima.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Artprima\QueryFilterBundle\Query\Condition;

use Doctrine\ORM\QueryBuilder;

/**
 * Class IsNotNull
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query\Condition
 */
class IsNotNull implements ConditionInterface
{
    public function getExpr(QueryBuilder $qb, string $field, int $index, array $val)
    {
        $expr = $qb->expr()->isNotNull($field);

        return $expr;
    }

    public function getName(): string
    {
        return 'is not null';
    }

}