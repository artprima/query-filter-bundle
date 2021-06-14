<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

use Artprima\QueryFilterBundle\Request\Request;

/**
 * Interface ConfigInterface.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
interface ConfigInterface
{
    /**
     * @return $this
     */
    public function setSearchAllowedCols(array $args): ConfigInterface;

    /**
     * Get allowed columns that are used in search.
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
     *
     * @return $this
     */
    public function setSearchByAliases(array $aliases): ConfigInterface;

    /**
     * Set Shortcut Expanders (for more info: {@link ConfigInterface::setSearchByAliases()}).
     */
    public function getSearchByAliases(): array;

    /**
     * Set extra data for search.
     *
     * @param callable $extra
     *
     * @return $this
     */
    public function setSearchByExtra(array $extra): ConfigInterface;

    /**
     * Get extra data for search.
     */
    public function getSearchByExtra(): array;

    /**
     * Set sort columns (allowed and default).
     *
     * @return $this
     */
    public function setSortCols(array $cols, array $default = []): ConfigInterface;

    /**
     * Get allowed sort columns.
     */
    public function getSortCols(): array;

    /**
     * Get default sort column data.
     */
    public function getSortColsDefault(): array;

    /**
     * Set repository callback [function($searchBy, $sortData, $limit, $offset)].
     *
     * @return $this
     */
    public function setRepositoryCallback(callable $callback): ConfigInterface;

    /**
     * Get repository callback [function($searchBy, $sortData, $limit, $offset)].
     */
    public function getRepositoryCallback(): callable;

    /**
     * @param array $allowedLimits allowed pagination limits (eg. [5, 10, 25, 50, 100])
     *
     * @return $this
     */
    public function setAllowedLimits(array $allowedLimits): ConfigInterface;

    /**
     * Get allowed pagination limits (eg. [5, 10, 25, 50, 100]).
     */
    public function getAllowedLimits(): array;

    /**
     * @var int default limit in case of limit not specified or limit is not within the allowed limits
     *
     * @return $this
     */
    public function setDefaultLimit(int $limit): ConfigInterface;

    /**
     * Get default limit in case of limit not specified or limit is not within the allowed limits.
     */
    public function getDefaultLimit(): int;

    public function setRequest(Request $request): ConfigInterface;

    /**
     * Get request data to build the filters.
     */
    public function getRequest(): Request;

    public function setStrictColumns(bool $strict): ConfigInterface;

    public function isStrictColumns(): bool;
}
