<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Artprima\QueryFilterBundle\QueryFilter\Config\QueryFilterConfigInterface;
use Artprima\QueryFilterBundle\Response\ResponseFactoryInterface;
use Artprima\QueryFilterBundle\Response\ResponseInterface;

/**
 * Class QueryFilter
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class QueryFilter
{
    /**
     * @var QueryFilterConfigInterface
     */
    private $config;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    public function __construct(QueryFilterConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Prepares data to use for sorting and paging
     *
     * Resulting 'sortdata' array element contains associated array where keys are column names and values are order
     * directions (only one array item is supported now, others are ignored). If no sorting provided, default sorting data is used.
     *
     * @return array Resulting array contains two elements 'curpage' and 'sortdata' as keys with corresponding values
     */
    private function getPageData(): array
    {
        $config = $this->config;

        $sort['field'] = $config->getRequest()->getSortBy();
        $sort['type'] = $config->getRequest()->getSortDir();
        $curPage = $config->getRequest()->getPageNum();

        if ($curPage < 1) {
            $curPage = 1;
        }

        $sortdata = $config->getSortColsDefault();
        if (isset($sort['field'], $sort['type'])) {
            if (in_array($sort['type'], array('asc', 'desc'), true) && in_array($sort['field'], $config->getSortCols(), true)) {
                $sortdata = array($sort['field'] => $sort['type']);
            }
        }

        return array(
            'curpage' => $curPage,
            'sortdata' => $sortdata,
        );
    }

    private function getSimpleSearchBy(array $allowedCols, ?array $search): array
    {
        $searchBy = [];

        if ($search === null) {
            return $searchBy;
        }

        foreach ($search as $key => $val) {
            if (in_array($key, $allowedCols, true) && $val !== null) {
                $searchBy[$key] = array(
                    'type' => 'like',
                    'val' => $val,
                );
                if (strpos($key, 'GroupConcat') !== false) {
                    $searchBy[$key]['having'] = true;
                }
            }
        }

        return $searchBy;
    }

    private function getFullSearchBy(array $allowedCols, ?array $search): array
    {
        $searchBy = [];

        foreach ($search as $data) {
            if (!empty($data) && is_array($data) && isset($data['field']) && in_array($data['field'], $allowedCols, true)) {
                $field = $data['field'];
                unset($data['field']);
                $searchBy[$field] = $data;
                if (strpos($field, 'GroupConcat') !== false) {
                    $searchBy[$field]['having'] = true;
                }
            }
        }

        return $searchBy;
    }

    /**
     * Get searchby data prepared for query builder
     *
     * If simple, $search must be set to:
     * <code>
     *     $this->searchData = array(
     *         'column_name1' => 'search_value1',
     *         'column_name2' => 'search_value2',
     *     );
     * </code>
     * All comparisons will be treated as "like" here.
     *
     * If not simple, $search must be set to:
     * <code>
     *     $this->searchData = array(
     *         array('field' => 'column_name1', 'type' => 'like', 'val' => 'search_value1'),
     *         array('field' => 'column_name2', 'type' => 'eq', 'val' => 'search_value2'),
     *     );
     * </code>
     *
     * For both cases GroupConcat columns the result will receive extra $searchBy["column_name1"]["having"] = true
     *
     * @param array $allowedCols
     * @param array|null $search
     * @param bool $simple
     * @return array
     */
    private function getSearchBy(array $allowedCols, ?array $search, bool $simple): array
    {
        return $simple ? $this->getSimpleSearchBy($allowedCols, $search) : $this->getFullSearchBy($allowedCols, $search);
    }

    /**
     * @param ResponseFactoryInterface $responseFactory
     */
    public function setResponseFactory(ResponseFactoryInterface $responseFactory): void
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Gets data for use in twig templates
     *
     * Consists of 8 steps:
     *
     * 1. Builds searchBy (see more: {@link QueryFilter::getSearchBy()}, {@link QueryFilterConfigInterface::getAllowedCols()},
     *   {@link QueryFilterConfigInterface::getSearchData()}, {@link QueryFilterConfigInterface::isSimpleSearch()})
     *
     * 2. Modifies searchBy array with the shortcut expanders (see more: {@link QueryFilterConfigInterface::getShortcutExpanders()})
     *
     * 3. Adds searchByExtra (if any) to the initial searchBy (see more: {@link QueryFilterConfigInterface::getSearchByExtra()})
     *
     * 4. Obtains paging and sorting data (see more: {@link QueryFilter::getSortPageData()}, {@link QueryFilterConfigInterface::getSortCols()},
     *    {@link QueryFilterConfigInterface::getCurPage()}, {@link QueryFilterConfigInterface::getSortBy()},
     *    {@link QueryFilterConfigInterface::getSortColsDefault()})
     *
     * 5. Obtains data to according to the conditions defined in steps 1-4 (see more:
     *    {@link QueryFilterConfigInterface::getRepositoryCallback()}, {@link QueryFilterConfigInterface::getItemsPerPage})
     *
     * 6. Prepares and retuns the response
     *
     * @todo: refactor to smaller parts
     *
     * @return ResponseInterface
     * @throws InvalidArgumentException
     */
    public function getData(): ResponseInterface
    {
        // shortcut to config
        $config = $this->config;

        // 1. Get  search by from query params limited by specified search by keys
        $searchBy = $this->getSearchBy(
            $config->getSearchAllowedCols(),
            $config->getRequest()->getQuery(),
            $config->getRequest()->isSimple()
        );

        // (optional) 2. replace search by shortcut(s) with more complicated expressions  // **
        $aliases = $config->getSearchByAliases();
        if (is_array($aliases)) {
            foreach ($aliases as $alias => $value) {
                if (!empty($searchBy[$alias])) {
                    if (!empty($value['data'])) {
                        $searchBy[$value['name']] = $value['data'];
                        $searchBy[$value['name']]['val'] = $searchBy[$alias]['val'];
                    } else {
                        $searchBy[$value['name']] = $searchBy[$alias];
                    }
                    unset($searchBy[$alias]);
                }
            }
        }

        // (optional) 3. Set search extra filters (can be used to display entries for one particular entity,
        //               or to add some extra conditions/filterings)
        $searchByExtra = $config->getSearchByExtra();
        if ($searchByExtra) {
            if (is_object($searchByExtra) && ($searchByExtra instanceof \Closure)) {
                $searchByExtra = $searchByExtra($searchBy);
            }
            $searchBy = array_merge($searchBy, $searchByExtra);
        }

        // 4. Obtain paging and sorting data
        $pageData = $this->getPageData();

        // 4.5 Replace spaces by %
        foreach ($searchBy as &$item) {
            if (is_array($item) && isset($item['type'], $item['val']) && ($item['type'] === 'like') && preg_match('/[\s\.,]+/', $item['val'])) {
                $words = preg_split('/[\s\.,]+/', $item['val']);
                $item['val'] = implode('%', $words);
            }
        }
        unset($item);

        // 5. Query database to obtain corresponding entities
        $repositoryCallback = $config->getRepositoryCallback();
        if (!\is_callable($repositoryCallback)) {
            throw new InvalidArgumentException('Repository callback is not callable');
        }
        $itemsPerPage = $config->getRequest()->getLimit();
        $args = (new QueryFilterArgs())
            ->setSearchBy($searchBy)
            ->setSortBy($pageData['sortdata'])
            ->setLimit($itemsPerPage)
            ->setOffset(($pageData['curpage'] - 1) * $itemsPerPage);

        // $repositoryCallback can be an array, but since PHP 7.0 it's possible to use it as a function directly
        // i.e. without using call_user_func[_array]().
        // For the reference: https://trowski.com/2015/06/20/php-callable-paradox/
        $startTime = microtime(true);
        $filterData = $repositoryCallback($args);
        $duration = microtime(true) - $startTime;
        if (!$filterData instanceof QueryResult) {
            throw new InvalidArgumentException('Repository callback must return an instance of QueryResult');
        }

        // 6. Prepare the data
        $result = $this->responseFactory->createResponse();
        $result->setData($filterData->getResult());
        $result->addMeta('total_records', $filterData->getTotalRows());

        $result->addMeta('metrics', array(
            'query_and_transformation' => $duration,
        ));

        // 7. Return the data
        return $result;
    }
}
