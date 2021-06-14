<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter;

/**
 * Class QueryResult.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class QueryResult
{
    /**
     * @var array
     */
    private $result;

    /**
     * @var int
     */
    private $totalRows;

    public function __construct(array $result, int $totalRows)
    {
        $this->result = $result;
        $this->totalRows = $totalRows;
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
