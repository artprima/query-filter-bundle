<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter;

/**
 * Class QueryResult.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class QueryResult
{
    public function __construct(private array $result, private int $totalRows)
    {
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }
}
