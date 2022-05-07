<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Request;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
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
        try {
            $this->pageNum = (int)$request->query->get('page', 1);
        } catch (BadRequestException) {
            throw new InvalidArgumentException('Query page must be scalar');
        }
        try {
            $this->limit = (int)$request->query->get('limit', -1);
        } catch (BadRequestException) {
            throw new InvalidArgumentException('Query limit must be scalar');
        }
        try {
            $this->query = $request->query->all('filter');
        } catch (BadRequestException) {
            throw new InvalidArgumentException('Query filter must be an array');
        }
        try {
            $this->sortBy = $request->query->get('sortby');
        } catch (BadRequestException) {
            throw new InvalidArgumentException('Query sort by must be scalar');
        }
        if (null !== $this->sortBy && !is_string($this->sortBy)) {
            throw new InvalidArgumentException('Query sort by must be a string');
        }
        try {
            $this->sortDir = $request->query->get('sortdir', 'asc');
        } catch (BadRequestException) {
            throw new InvalidArgumentException('Query sort direction must be scalar');
        }
        if (!is_string($this->sortDir)) {
            throw new InvalidArgumentException('Query sort direction must be a string');
        }
        try {
            $this->simple = (bool)$request->query->get('simple', '1');
        } catch (BadRequestException) {
            throw new InvalidArgumentException('Query simple must be scalar');
        }
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
