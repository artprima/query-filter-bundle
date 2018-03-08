<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Request;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Class Request
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
     * @param HttpRequest $request
     * @todo consider paramenter names not to be hard-coded
     */
    public function __construct(HttpRequest $request)
    {
        $this->pageNum = (int)$request->query->get('page', 1);
        $this->limit = (int)$request->query->get('limit', -1);
        $this->query = $request->query->get('filter');
        if ($this->query !== null && !is_array($this->query)) {
            throw new InvalidArgumentException('Query filter must be an array');
        }
        $this->sortBy = $request->query->get('sortby');
        $this->sortDir = $request->query->get('sortdir', 'asc');
        if (!is_string($this->sortDir) || !in_array(strtolower($this->sortDir), ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Query sort direction must be one of those: asc or desc');
        }
        $this->simple = (bool)$request->query->get('simple', true);
    }

    /**
     * @return int
     */
    public function getPageNum(): int
    {
        return $this->pageNum;
    }

    /**
     * @return string|null
     */
    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    /**
     * @return string|null
     */
    public function getSortDir(): ?string
    {
        return $this->sortDir;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return array|null
     */
    public function getQuery(): ?array
    {
        return $this->query;
    }

    /**
     * @return bool
     */
    public function isSimple(): bool
    {
        return $this->simple;
    }
}
