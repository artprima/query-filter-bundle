<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\QueryFilter;

/**
 * Class QueryResult
 *
 * @author Denis Voytyuk <denis@voituk.ru>
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

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @return int
     */
    public function getTotalRows(): int
    {
        return $this->totalRows;
    }
}
