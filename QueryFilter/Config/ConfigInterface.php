<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

use Artprima\QueryFilterBundle\Request\Request;

/**
 * Interface ConfigInterface
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package AppBundle\QueryFilter
 */
interface ConfigInterface
{
    /**
     * @param array $args
     * @return $this
     */
    public function setSearchAllowedCols(array $args): ConfigInterface;

    /**
     * Get allowed columns that are used in search
     *
     * @return array
     */
    public function getSearchAllowedCols(): array;

    /**
     * Set shortcut expanders (aliaces).
     *
     * For example, concat(concat(concat(concat(p.firstname, ' '), p.middlename), ' '), p.lastname) can be
     * aliased by 'person_name':
     *
     * @see QueryFilter::getTemplateData()
     *
     * @param Alias[] $aliases
     * @return $this
     */
    public function setSearchByAliases(array $aliases): ConfigInterface;

    /**
     * Set Shortcut Expanders (for more info: {@link ConfigInterface::setSearchByAliases()})
     *
     * @return array
     */
    public function getSearchByAliases(): array;

    /**
     * Set extra data for search
     *
     * @param callable $extra
     * @return $this
     */
    public function setSearchByExtra(array $extra): ConfigInterface;

    /**
     * Get extra data for search
     *
     * @return array
     */
    public function getSearchByExtra(): array;

    /**
     * Set sort columns (allowed and default)
     *
     * @param array $cols
     * @param array $default
     * @return $this
     */
    public function setSortCols(array $cols, array $default = array()): ConfigInterface;

    /**
     * Get allowed sort columns
     * @return array
     */
    public function getSortCols(): array;

    /**
     * Get default sort column data
     *
     * @return array
     */
    public function getSortColsDefault(): array;

    /**
     * Set repository callback [function($searchBy, $sortData, $limit, $offset)]
     *
     * @param callable $callback
     * @return $this
     */
    public function setRepositoryCallback(callable $callback): ConfigInterface;

    /**
     * Get repository callback [function($searchBy, $sortData, $limit, $offset)]
     *
     * @return callable
     */
    public function getRepositoryCallback(): callable;

    /**
     * @param array $allowedLimits allowed pagination limits (eg. [5, 10, 25, 50, 100])
     * @return $this
     */
    public function setAllowedLimits(array $allowedLimits): ConfigInterface;

    /**
     * Get allowed pagination limits (eg. [5, 10, 25, 50, 100])
     *
     * @return array
     */
    public function getAllowedLimits(): array;

    /**
     * @var int $limit default limit in case of limit not specified or limit is not within the allowed limits
     *
     * @return $this
     */
    public function setDefaultLimit(int $limit): ConfigInterface;

    /**
     * Get default limit in case of limit not specified or limit is not within the allowed limits
     *
     * @return int
     */
    public function getDefaultLimit(): int;

    /**
     * @param Request $request
     * @return ConfigInterface
     */
    public function setRequest(Request $request): ConfigInterface;

    /**
     * Get request data to build the filters
     *
     * @return Request
     */
    public function getRequest(): Request;
}
