<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

use Artprima\QueryFilterBundle\Request\Request;

/**
 * Interface QueryFilterConfigInterface
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package AppBundle\QueryFilter
 */
interface QueryFilterConfigInterface
{
    /**
     * @param array $args
     * @return $this
     */
    public function setSearchAllowedCols(array $args): QueryFilterConfigInterface;

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
     * <code>
     * array(
     *     'person_name' => array(
     *         'name' => "concat(concat(concat(concat(p.firstname, ' '), p.middlename), ' '), p.lastname)",
     *         'data' => array(
     *             'having' => true,
     *             'type' => 'like',
     *         ),
     *     ),
     * );
     * </code>
     *
     * 'data' parameter is optional but necessary when you cannot use "where" or want to change the type of comparison
     *
     * @see QueryFilter::getTemplateData()
     *
     * @param array $expanders
     * @return $this
     */
    public function setSearchByAliases(array $expanders): QueryFilterConfigInterface;

    /**
     * Set Shortcut Expanders (for more info: {@link QueryFilterConfigInterface::setShortcutExpanders()})
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
    public function setSearchByExtra(callable $extra): QueryFilterConfigInterface;

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
    public function setSortCols(array $cols, array $default = array()): QueryFilterConfigInterface;

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
    public function setRepositoryCallback(callable $callback): QueryFilterConfigInterface;

    /**
     * Get repository callback [function($searchBy, $sortData, $limit, $offset)]
     *
     * @return callable
     */
    public function getRepositoryCallback(): callable ;

    /**
     * @param array $allowedLimits allowed pagination limits (eg. [5, 10, 25, 50, 100])
     * @return $this
     */
    public function setAllowedLimits(array $allowedLimits): QueryFilterConfigInterface;

    /**
     * Get allowed pagination limits (eg. [5, 10, 25, 50, 100])
     *
     * @return array
     */
    public function getAllowedLimits(): array;

    /**
     * @param callable $totalRowsCallback
     * @return $this
     */
    public function setTotalRowsCallback(callable $totalRowsCallback): QueryFilterConfigInterface;

    /**
     * @return callable
     */
    public function getTotalRowsCallback(): callable;

    /**
     * @param Request $requestData
     * @return QueryFilterConfigInterface
     */
    public function setRequest(Request $requestData): QueryFilterConfigInterface;

    /**
     * Get request data to build the filters
     *
     * @return Request
     */
    public function getRequest(): Request;
}
