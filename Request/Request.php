<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Request;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Class Request.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class Request
{
    /**
     * @var int
     */
    private $pageNum;

    /**
     * @var string
     */
    private $sortBy;

    /**
     * @var string
     */
    private $sortDir;

    /**
     * @var array
     */
    private $query;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var bool
     */
    private $simple;

    /**
     * Request constructor.
     *
     * @todo consider paramenter names not to be hard-coded
     */
    public function __construct(HttpRequest $request)
    {
        $this->pageNum = (int) $request->query->get('page', 1);
        $this->limit = (int) $request->query->get('limit', -1);
        $this->query = $request->query->get('filter');
        if (null !== $this->query && !is_array($this->query)) {
            throw new InvalidArgumentException('Query filter must be an array');
        }
        $this->sortBy = $request->query->get('sortby');
        $this->sortDir = $request->query->get('sortdir', 'asc');
        if (!is_string($this->sortDir)) {
            throw new InvalidArgumentException('Query sort direction must be a string');
        }
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
