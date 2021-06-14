<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

use Artprima\QueryFilterBundle\Exception\MissingArgumentException;
use Artprima\QueryFilterBundle\Request\Request;

/**
 * Class BaseConfig.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class BaseConfig implements ConfigInterface
{
    private const DEFAULT_LIMIT = 10;

    protected Request $request;
    protected int $defaultLimit = self::DEFAULT_LIMIT;
    protected array $allowedLimits = [];
    protected array $searchBy = [
        'args' => [],
        'aliases' => [],
        'extra' => [],
    ];
    protected array $sort = [
        'cols' => [],
        'default' => [],
    ];

    /**
     * @var callable
     */
    protected $repositoryCallback;

    /**
     * @var callable
     */
    protected $totalRowsCallback;

    protected bool $simple = true;
    protected bool $strictColumns = false;

    /**
     * {@inheritdoc}
     */
    public function setSearchAllowedCols(array $args): self
    {
        $this->searchBy['args'] = $args;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchAllowedCols(): array
    {
        return $this->searchBy['args'];
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchByAliases(array $aliases): self
    {
        /** @var Alias $alias */
        foreach ($aliases as $alias) {
            $this->searchBy['aliases'][$alias->getName()] = $alias;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSearchByAliases(Alias $alias): self
    {
        $this->searchBy['aliases'][$alias->getName()] = $alias;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchByAliases(): array
    {
        return $this->searchBy['aliases'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchByExtra(array $extra): self
    {
        $this->searchBy['extra'] = $extra;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchByExtra(): array
    {
        return $this->searchBy['extra'];
    }

    /**
     * {@inheritdoc}
     */
    public function setSortCols(array $cols, array $default = []): self
    {
        $this->sort['cols'] = $cols;
        $this->sort['default'] = $default;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortCols(): array
    {
        return $this->sort['cols'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSortColsDefault(): array
    {
        return $this->sort['default'];
    }

    /**
     * {@inheritdoc}
     */
    public function setRepositoryCallback(callable $callback): self
    {
        $this->repositoryCallback = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryCallback(): callable
    {
        if (null === $this->repositoryCallback) {
            throw new MissingArgumentException('Repository callback is not set');
        }

        return $this->repositoryCallback;
    }

    public function getAllowedLimits(): array
    {
        return $this->allowedLimits;
    }

    public function setAllowedLimits(array $allowedLimits): self
    {
        $this->allowedLimits = $allowedLimits;

        return $this;
    }

    public function setDefaultLimit(int $limit): self
    {
        $this->defaultLimit = $limit;

        return $this;
    }

    public function getDefaultLimit(): int
    {
        return $this->defaultLimit;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function setStrictColumns(bool $strict): self
    {
        $this->strictColumns = $strict;

        return $this;
    }

    public function isStrictColumns(): bool
    {
        return $this->strictColumns;
    }
}
