<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

use Artprima\QueryFilterBundle\Exception\MissingArgumentException;
use Artprima\QueryFilterBundle\Query\Filter;
use Artprima\QueryFilterBundle\QueryFilter\Request;

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

    protected $searchAllowedFields = [];

    /**
     * @var Alias[]
     */
    protected $searchAliases = [];

    /**
     * @var Filter[]
     */
    protected $extraFilters = [];

    protected $sortFields = [];
    protected $sortDefaults = [];

    /**
     * @var callable
     */
    protected $repositoryCallback;

    protected bool $simple = true;
    protected bool $strictColumns = false;

    /**
     * {@inheritdoc}
     */
    public function setSearchAllowedFields(array $args): self
    {
        $this->searchAllowedFields = $args;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchAllowedFields(): array
    {
        return $this->searchAllowedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchAliases(array $aliases): self
    {
        $this->searchAliases = (function (Alias ...$aliases) {
            $result = [];
            foreach ($aliases as $alias) {
                $result[$alias->getName()] = $alias;
            }
            return $result;
        })(...$aliases);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSearchAliases(Alias $alias): self
    {
        $this->searchAliases[$alias->getName()] = $alias;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchAliases(): array
    {
        return $this->searchAliases ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function setExtraFilters(array $extra): self
    {
        $this->extraFilters = (fn (Filter ...$items) => $items)(...$extra);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraFilters(): array
    {
        return $this->extraFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortFields(array $cols): self
    {
        $this->sortFields = $cols;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortFields(): array
    {
        return $this->sortFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortDefaults(): array
    {
        return $this->sortDefaults;
    }

    /**
     * {@inheritdoc}
     */
    public function setSortDefaults(array $sortDefaults): self
    {
        $this->sortDefaults = $sortDefaults;

        return $this;
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
