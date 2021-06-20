<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

use Artprima\QueryFilterBundle\Query\Filter;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilterArgs;
use Artprima\QueryFilterBundle\QueryFilter\QueryResult;
use Artprima\QueryFilterBundle\QueryFilter\Request;

/**
 * Interface ConfigInterface.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
interface ConfigInterface
{
    /**
     * @param string[]
     */
    public function setSearchAllowedFields(array $args): self;

    /**
     * Get allowed columns that are used in search.
     */
    public function getSearchAllowedFields(): array;

    /**
     * Set shortcut expanders (aliaces).
     *
     * For example, concat(concat(concat(concat(p.firstname, ' '), p.middlename), ' '), p.lastname) can be
     * aliased as 'person_name':
     *
     * @see QueryFilter::getTemplateData()
     *
     * @param Alias[] $aliases
     *
     * @return $this
     */
    public function setSearchAliases(array $aliases): self;

    /**
     * Set Shortcut Expanders (for more info: {@link ConfigInterface::setSearchAliases()}).
     */
    public function getSearchAliases(): array;

    /**
     * Set extra data for search (can be used by.
     * @param Filter[] $extra
     */
    public function setExtraFilters(array $extra): self;

    /**
     * Get extra data for search.
     * @return Filter[]
     */
    public function getExtraFilters(): array;

    /**
     * Set sort columns (allowed and default).
     *
     * @return $this
     */
    public function setSortFields(array $cols): self;

    /**
     * Get allowed sort columns.
     */
    public function getSortFields(): array;

    /**
     * Set default sort column data.
     */
    public function setSortDefaults(array $defaults): self;

    /**
     * Get default sort column data.
     */
    public function getSortDefaults(): array;

    /**
     * Set repository callback [function($searchBy, $sortData, $limit, $offset)].
     *
     * @return $this
     */
    public function setRepositoryCallback(callable $callback): self;

    /**
     * Get repository callback [function (QueryFilterArgs $args): QueryResult].
     */
    public function getRepositoryCallback(): callable;

    /**
     * @param array $allowedLimits allowed pagination limits (eg. [5, 10, 25, 50, 100])
     *
     * @return $this
     */
    public function setAllowedLimits(array $allowedLimits): self;

    /**
     * Get allowed pagination limits (eg. [5, 10, 25, 50, 100]).
     */
    public function getAllowedLimits(): array;

    /**
     * @var int default limit in case of limit not specified or limit is not within the allowed limits
     *
     * @return $this
     */
    public function setDefaultLimit(int $limit): self;

    /**
     * Get default limit in case of limit not specified or limit is not within the allowed limits.
     */
    public function getDefaultLimit(): int;

    public function setRequest(Request $request): self;

    /**
     * Get request data to build the filters.
     */
    public function getRequest(): Request;

    public function setStrictColumns(bool $strict): self;

    public function isStrictColumns(): bool;
}
