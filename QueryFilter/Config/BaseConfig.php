<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

use Artprima\QueryFilterBundle\Request\Request;

/**
 * Class BaseConfig
 *
 * @author Denis Voytyuk <denis@voituk.ru>
 *
 * @package Artprima\QueryFilterBundle\QueryFilter\Config
 */
class BaseConfig implements ConfigInterface
{
    private const DEFAULT_LIMIT = 10;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var int
     */
    protected $defaultLimit;

    /**
     * @var array
     */
    protected $allowedLimits;

    /**
     * @var array
     */
    protected $searchBy = [
        'args' => [],
        'aliases' => [],
        'extra' => [],
    ];

    /**
     * @var array
     */
    protected $sort = [
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

    /**
     * @var bool
     */
    protected $simple = true;

    /**
     * @inheritdoc
     */
    public function setSearchAllowedCols(array $args): ConfigInterface
    {
        $this->searchBy['args'] = $args;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchAllowedCols(): array
    {
        return $this->searchBy['args'];
    }

    /**
     * @inheritdoc
     */
    public function setSearchByAliases(array $aliases): ConfigInterface
    {
        /** @var Alias $alias */
        foreach ($aliases as $alias) {
            $this->searchBy['aliases'][$alias->getName()] = $alias;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addSearchByAliases(Alias $alias): ConfigInterface
    {
        $this->searchBy['aliases'][$alias->getName()] = $alias;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchByAliases(): array
    {
        return $this->searchBy['aliases'] ?? [];
    }

    /**
     * @inheritdoc
     */
    public function setSearchByExtra(array $extra): ConfigInterface
    {
        $this->searchBy['extra'] = $extra;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSearchByExtra(): array
    {
        return $this->searchBy['extra'];
    }

    /**
     * @inheritdoc
     */
    public function setSortCols(array $cols, array $default = []): ConfigInterface
    {
        $this->sort['cols'] = $cols;
        $this->sort['default'] = $default;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSortCols(): array
    {
        return $this->sort['cols'];
    }

    /**
     * @inheritdoc
     */
    public function getSortColsDefault(): array
    {
        return $this->sort['default'];
    }

    /**
     * @inheritdoc
     */
    public function setRepositoryCallback(callable $callback): ConfigInterface
    {
        $this->repositoryCallback = $callback;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRepositoryCallback(): callable
    {
        return $this->repositoryCallback;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedLimits(): array
    {
        return $this->allowedLimits ?? [];
    }

    /**
     * @inheritdoc
     */
    public function setAllowedLimits(array $allowedLimits): ConfigInterface
    {
        $this->allowedLimits = $allowedLimits;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDefaultLimit(int $limit): ConfigInterface
    {
        $this->defaultLimit = $limit;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultLimit(): int
    {
        return $this->defaultLimit ?? self::DEFAULT_LIMIT;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @inheritdoc
     */
    public function setRequest(Request $request): ConfigInterface
    {
        $this->request = $request;

        return $this;
    }
}
