<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter;

final class QueryFilterArgs
{
    private array $searchBy;
    private array $sortBy;
    private int $offset;
    private int $limit;

    public function getSearchBy(): array
    {
        return $this->searchBy;
    }

    public function setSearchBy(array $searchBy): self
    {
        $this->searchBy = $searchBy;

        return $this;
    }

    public function getSortBy(): array
    {
        return $this->sortBy;
    }

    public function setSortBy(array $sortBy): self
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }
}
