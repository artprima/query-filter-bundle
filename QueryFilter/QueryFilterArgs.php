<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter;

class QueryFilterArgs
{
    /**
     * @var array
     */
    private $searchBy;

    /**
     * @var array
     */
    private $sortBy;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    public function getSearchBy(): array
    {
        return $this->searchBy;
    }

    /**
     * @return $this
     */
    public function setSearchBy(array $searchBy): QueryFilterArgs
    {
        $this->searchBy = $searchBy;

        return $this;
    }

    public function getSortBy(): array
    {
        return $this->sortBy;
    }

    /**
     * @return $this
     */
    public function setSortBy(array $sortBy): QueryFilterArgs
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return $this
     */
    public function setOffset(int $offset): QueryFilterArgs
    {
        $this->offset = $offset;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return $this
     */
    public function setLimit(int $limit): QueryFilterArgs
    {
        $this->limit = $limit;

        return $this;
    }
}
