<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Artprima\QueryFilterBundle\Exception\InvalidLengthException;
use Artprima\QueryFilterBundle\Exception\UnexpectedValueException;
use Artprima\QueryFilterBundle\Query\Filter;
use Artprima\QueryFilterBundle\QueryFilter\Config\Alias;
use Artprima\QueryFilterBundle\QueryFilter\Config\ConfigInterface;
use Artprima\QueryFilterBundle\Response\ResponseInterface;

/**
 * Class QueryFilter
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class QueryFilter
{
    /**
     * @var string
     */
    private $responseClassName;

    /**
     * QueryFilter constructor.
     * @param string $responseClassName
     * @throws \ReflectionException
     * @throws InvalidArgumentException
     */
    public function __construct(string $responseClassName)
    {
        $refClass = new \ReflectionClass($responseClassName);
        if (!$refClass->implementsInterface(ResponseInterface::class)) {
            throw new InvalidArgumentException(sprintf(
                'Response class "%s" must implement "%s"',
                $responseClassName,
                ResponseInterface::class
            ));
        }

        $constructor = $refClass->getConstructor();
        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new InvalidArgumentException(sprintf(
                'Response class "%s" must have a constructor without required parameters',
                $responseClassName
            ));
        }

        $this->responseClassName = $responseClassName;
    }

    /**
     * @param ConfigInterface $config
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

    /**
     * @param ConfigInterface $config
     * @return array
     */
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
        $isValidSortType = in_array($sort['type'], array('asc', 'desc'), true);

        if ($isValidSortColumn && $isValidSortType) {
            return array($sort['field'] => $sort['type']);
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
     * @param string $field
     * @param array|string $val
     * @return Filter
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
        $filter->setHaving((bool)($val['having'] ?? false));

        return $filter;
    }

    private function checkFilterLengths(Filter $filter, array $allowedColsLengths): void
    {
        if (!is_array($fieldLengths = $allowedColsLengths[$filter->getField()] ?? null)) {
            return;
        }

        if (isset($fieldLengths['min'])) {
            if (
                (null !== $filter->getX() && strlen($filter->getX()) < $fieldLengths['min'])
                    || (null !== $filter->getY() && strlen($filter->getY()) < $fieldLengths['min'])) {
                throw new InvalidLengthException(sprintf('Invalid filter min length requested %s', $filter->getField()));
            }
        }

        if (isset($fieldLengths['max'])) {
            if (
                (null !== $filter->getX() && strlen($filter->getX()) > $fieldLengths['max'])
                    || (null !== $filter->getY() && strlen($filter->getY()) > $fieldLengths['max'])) {
                throw new InvalidLengthException(sprintf('Invalid filter max length requested %s', $filter->getField()));
            }
        }
    }

    /**
     * @param array $allowedCols
     * @param array $allowedColsLengths
     * @param array|null $search
     * @param bool $throw
     * @return Filter[]
     */
    private function getSimpleSearchBy(array $allowedCols, array $allowedColsLengths, ?array $search, bool $throw): array
    {
        /** @var Filter[] $searchBy */
        $searchBy = [];

        if ($search === null) {
            return $searchBy;
        }

        foreach ($search as $key => $val) {
            if (in_array($key, $allowedCols, true) && $val !== null) {
                $filter = $this->getFilter($key, $val);

                $this->checkFilterLengths($filter, $allowedColsLengths);

                $searchBy[] = $filter;
                continue;
            }

            if ($throw) {
                throw new UnexpectedValueException(sprintf('Invalid filter column requested %s', $key));
            }
        }

        return $searchBy;
    }

    /**
     * @param array $allowedCols
     * @param array $allowedColsLengths
     * @param array|null $search
     * @param bool $throw
     * @return Filter[]
     */
    private function getFullSearchBy(array $allowedCols, array $allowedColsLengths, ?array $search, bool $throw): array
    {
        /** @var Filter[] $searchBy */
        $searchBy = [];

        if ($search === null) {
            return $searchBy;
        }

        foreach ($search as $key => $data) {
            $valid = is_array($data) && isset($data['field']) && in_array($data['field'], $allowedCols, true);
            if (!$valid && $throw) {
                throw new UnexpectedValueException(sprintf('Invalid filter column requested %s', $data['field'] ?? '['.$key.']'));
            }
            if ($valid) {
                $filter = $this->getFilter($data['field'], $data);

                $this->checkFilterLengths($filter, $allowedColsLengths);

                $searchBy[$key] = $filter;
            }
        }

        return $searchBy;
    }

    /**
     * @param Filter[] $searchBy
     * @param Alias[] $aliases
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
     * @param ConfigInterface $config
     * @return array
     */
    private function getSearchBy(ConfigInterface $config): array
    {
        // Get basic search by
        $searchBy = $config->getRequest()->isSimple()
            ? $this->getSimpleSearchBy($config->getSearchAllowedCols(), $config->getSearchAllowedColsLengths(), $config->getRequest()->getQuery(), $config->isStrictColumns())
            : $this->getFullSearchBy($config->getSearchAllowedCols(), $config->getSearchAllowedColsLengths(), $config->getRequest()->getQuery(), $config->isStrictColumns());

        // Set search aliases to more complicated expressions
        $this->replaceSearchByAliases($searchBy, $config->getSearchByAliases());

        // Set search extra filters (can be used to display entries for one particular entity,
        // or to add some extra conditions/filterings)
        $searchBy = array_merge($config->getSearchByExtra(), $searchBy);

        return $searchBy;
    }

    /**
     * @param ConfigInterface $config
     * @return QueryFilterArgs
     */
    private function getQueryFilterArgs(ConfigInterface $config): QueryFilterArgs
    {
        $searchBy = $this->getSearchBy($config);
        $currentPage = $this->getCurrentPage($config);
        $sortData = $this->getSortData($config);

        $limit = $config->getRequest()->getLimit();
        $allowedLimits = $config->getAllowedLimits();
        if ($limit === -1 || !in_array($limit, $allowedLimits, true)) {
            $limit = $config->getDefaultLimit();
        }

        $args = (new QueryFilterArgs())
            ->setSearchBy($searchBy)
            ->setSortBy($sortData)
            ->setLimit($limit)
            ->setOffset(($currentPage - 1) * $limit);

        return $args;
    }

    /**
     * @param ConfigInterface $config
     * @param QueryFilterArgs $args
     * @return QueryResult
     */
    private function getFilterData(ConfigInterface $config, QueryFilterArgs $args): QueryResult
    {
        return $config->getRepositoryCallback()($args);
    }

    /**
     * Gets filtered data
     *
     * @param ConfigInterface $config
     * @return ResponseInterface
     */
    public function getData(ConfigInterface $config): ResponseInterface
    {
        $args = $this->getQueryFilterArgs($config);

        $startTime = microtime(true);
        $filterData = $this->getFilterData($config, $args);
        $duration = microtime(true) - $startTime;

        /** @var ResponseInterface $response */
        $response = new $this->responseClassName;
        $response->setData($filterData->getResult());
        $response->addMeta('total_records', $filterData->getTotalRows());
        $response->addMeta('metrics', array(
            'query_and_transformation' => $duration,
        ));

        return $response;
    }
}
