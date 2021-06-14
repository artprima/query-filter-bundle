<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Artprima\QueryFilterBundle\Exception\UnexpectedValueException;
use Artprima\QueryFilterBundle\Query\Filter;
use Artprima\QueryFilterBundle\QueryFilter\Config\Alias;
use Artprima\QueryFilterBundle\QueryFilter\Config\ConfigInterface;
use Artprima\QueryFilterBundle\Response\ResponseInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Class QueryFilter.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class QueryFilter
{
    /**
     * QueryFilter constructor.
     *
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function __construct(private string $responseClassName)
    {
        $refClass = new ReflectionClass($responseClassName);
        if (!$refClass->implementsInterface(ResponseInterface::class)) {
            throw new InvalidArgumentException(sprintf('Response class "%s" must implement "%s"', $responseClassName, ResponseInterface::class));
        }

        $constructor = $refClass->getConstructor();
        if (null !== $constructor && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new InvalidArgumentException(sprintf('Response class "%s" must have a constructor without required parameters', $responseClassName));
        }
    }

    /**
     * @return int current page number
     */
    private function getCurrentPage(ConfigInterface $config): int
    {
        $curPage = $config->getRequest()->getPageNum();

        if ($curPage < 1) {
            $curPage = 1;
        }

        return $curPage;
    }

    private function getSortData(ConfigInterface $config): array
    {
        $sort = [
            'field' => $config->getRequest()->getSortBy(),
            'type' => $config->getRequest()->getSortDir(),
        ];

        if (!isset($sort['field'], $sort['type'])) {
            return $config->getSortColsDefault();
        }

        $isValidSortColumn = in_array($sort['field'], $config->getSortCols(), true);
        $isValidSortType = in_array($sort['type'], ['asc', 'desc'], true);

        if ($isValidSortColumn && $isValidSortType) {
            return [$sort['field'] => $sort['type']];
        }

        if ($config->isStrictColumns() && !$isValidSortColumn) {
            throw new UnexpectedValueException(sprintf('Invalid sort column requested %s', $sort['field']));
        }

        if ($config->isStrictColumns() && !$isValidSortType) {
            throw new UnexpectedValueException(sprintf('Invalid sort type requested %s', $sort['type']));
        }

        // we should never reach this point, but let's keep it
        return $config->getSortColsDefault();
    }

    /**
     * @param array|string $val
     */
    private function getFilter(string $field, $val): Filter
    {
        $filter = new Filter();
        if (!is_array($val)) {
            $val = [
                'x' => $val,
                'type' => 'like',
            ];
        }

        $filter->setField($field);
        $filter->setType($val['type'] ?? 'like');
        $filter->setX($val['x'] ?? null);
        $filter->setY($val['y'] ?? null);
        $filter->setExtra($val['extra'] ?? null);
        $filter->setConnector($val['connector'] ?? 'and');
        $filter->setHaving((bool) ($val['having'] ?? false));

        return $filter;
    }

    /**
     * @return Filter[]
     */
    private function getSimpleSearchBy(array $allowedCols, ?array $search, bool $throw): array
    {
        /** @var Filter[] $searchBy */
        $searchBy = [];

        if (null === $search) {
            return $searchBy;
        }

        foreach ($search as $key => $val) {
            if (in_array($key, $allowedCols, true) && null !== $val) {
                $searchBy[] = $this->getFilter($key, $val);
                continue;
            }

            if ($throw) {
                throw new UnexpectedValueException(sprintf('Invalid filter column requested %s', $key));
            }
        }

        return $searchBy;
    }

    /**
     * @return Filter[]
     */
    private function getFullSearchBy(array $allowedCols, ?array $search, bool $throw): array
    {
        /** @var Filter[] $searchBy */
        $searchBy = [];

        if (null === $search) {
            return $searchBy;
        }

        foreach ($search as $key => $data) {
            $valid = is_array($data) && isset($data['field']) && in_array($data['field'], $allowedCols, true);
            if (!$valid && $throw) {
                throw new UnexpectedValueException(sprintf('Invalid filter column requested %s', $data['field'] ?? '['.$key.']'));
            }
            if ($valid) {
                $searchBy[$key] = $this->getFilter($data['field'], $data);
            }
        }

        return $searchBy;
    }

    /**
     * @param Filter[] $searchBy
     * @param Alias[]  $aliases
     */
    private function replaceSearchByAliases(array $searchBy, array $aliases)
    {
        foreach ($searchBy as $filter) {
            if (array_key_exists($filter->getField(), $aliases)) {
                $filter->setField($aliases[$filter->getField()]->getExpr());
            }
        }
    }

    /**
     * Get searchby data prepared for query builder.
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
     */
    private function getSearchBy(ConfigInterface $config): array
    {
        // Get basic search by
        $searchBy = $config->getRequest()->isSimple()
            ? $this->getSimpleSearchBy($config->getSearchAllowedCols(), $config->getRequest()->getQuery(), $config->isStrictColumns())
            : $this->getFullSearchBy($config->getSearchAllowedCols(), $config->getRequest()->getQuery(), $config->isStrictColumns());

        // Set search aliases to more complicated expressions
        $this->replaceSearchByAliases($searchBy, $config->getSearchByAliases());

        // Set search extra filters (can be used to display entries for one particular entity,
        // or to add some extra conditions/filterings)
        $searchBy = array_merge($config->getSearchByExtra(), $searchBy);

        return $searchBy;
    }

    private function getQueryFilterArgs(ConfigInterface $config): QueryFilterArgs
    {
        $searchBy = $this->getSearchBy($config);
        $currentPage = $this->getCurrentPage($config);
        $sortData = $this->getSortData($config);

        $limit = $config->getRequest()->getLimit();
        $allowedLimits = $config->getAllowedLimits();
        if (-1 === $limit || !in_array($limit, $allowedLimits, true)) {
            $limit = $config->getDefaultLimit();
        }

        $args = (new QueryFilterArgs())
            ->setSearchBy($searchBy)
            ->setSortBy($sortData)
            ->setLimit($limit)
            ->setOffset(($currentPage - 1) * $limit);

        return $args;
    }

    private function getFilterData(ConfigInterface $config, QueryFilterArgs $args): QueryResult
    {
        return $config->getRepositoryCallback()($args);
    }

    /**
     * Gets filtered data.
     */
    public function getData(ConfigInterface $config): ResponseInterface
    {
        $args = $this->getQueryFilterArgs($config);

        $startTime = microtime(true);
        $filterData = $this->getFilterData($config, $args);
        $duration = microtime(true) - $startTime;

        /** @var ResponseInterface $response */
        $response = new $this->responseClassName();
        $response->setData($filterData->getResult());
        $response->addMeta('total_records', $filterData->getTotalRows());
        $response->addMeta('metrics', [
            'query_and_transformation' => $duration,
        ]);

        return $response;
    }
}
