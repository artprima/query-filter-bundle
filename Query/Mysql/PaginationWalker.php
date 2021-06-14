<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query\Mysql;

use Doctrine\ORM\Query\AST\SelectClause;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Class PaginationWalker.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class PaginationWalker extends SqlWalker
{
    /**
     * Walks down a SelectClause AST node, thereby generating the appropriate SQL.
     *
     * @param SelectClause $selectClause
     *
     * @return string The SQL.
     *
     * Usage:
     *
     * <pre>
     * $query->setHint(DoctrineQuery::HINT_CUSTOM_OUTPUT_WALKER, PaginationWalker::class);
     * $query->setHint('mysqlWalker.sqlCalcFoundRows', true);
     * </pre>
     */
    public function walkSelectClause($selectClause): string
    {
        $sql = parent::walkSelectClause($selectClause);

        if (true === $this->getQuery()->getHint('mysqlWalker.sqlCalcFoundRows')) {
            if ($selectClause->isDistinct) {
                $sql = str_replace('SELECT DISTINCT', 'SELECT DISTINCT SQL_CALC_FOUND_ROWS', $sql);
            } else {
                $sql = str_replace('SELECT', 'SELECT SQL_CALC_FOUND_ROWS', $sql);
            }
        }

        return $sql;
    }
}
