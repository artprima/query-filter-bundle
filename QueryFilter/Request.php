<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Class Request.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class Request
{
    private int $pageNum;
    private ?string $sortBy;
    private ?string $sortDir;
    private ?array $query;
    private int $limit;
    private bool $simple;

    /**
     * Request constructor.
     *
     * @todo consider paramenter names not to be hard-coded
     */
    public function __construct(HttpRequest $request)
    {
        $this->pageNum = (int) $request->query->get('page', 1);
        $this->limit = (int) $request->query->get('limit', -1);

        $query = $request->query->get('filter');
        if (null !== $query && !is_array($query)) {
            throw new InvalidArgumentException('Query filter must be an array');
        }
        $this->query = $query;

        $sortBy = (string) $request->query->get('sortby');
        if (null !== $sortBy && !is_string($sortBy)) {
            throw new InvalidArgumentException('Sort by must be string or null');
        }
        $this->sortBy = $sortBy;

        $sortDir = $request->query->get('sortdir', 'asc');
        if (null !== $sortDir && !is_string($sortDir)) {
            throw new InvalidArgumentException('Query sort direction must be string or null');
        }
        $this->sortDir = $sortDir;

        $this->simple = (bool) $request->query->get('simple', '1');
    }

    public function getPageNum(): int
    {
        return $this->pageNum;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function getSortDir(): ?string
    {
        return $this->sortDir;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getQuery(): ?array
    {
        return $this->query;
    }

    public function isSimple(): bool
    {
        return $this->simple;
    }
}
