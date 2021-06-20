<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;
use Stringable;

/**
 * Interface ConditionInterface.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
interface ConditionInterface
{
    public function getExpr(QueryBuilder $qb, int $index, Filter $filter): string|Stringable;
}
