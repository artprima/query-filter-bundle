<?php declare(strict_types=1);

namespace Tests\Artprima\QueryFilterBundle\Request;

use Artprima\QueryFilterBundle\Query\Filter;
use Artprima\QueryFilterBundle\QueryFilter\Config\Alias;
use Artprima\QueryFilterBundle\QueryFilter\Config\BaseConfig;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilter;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilterArgs;
use Artprima\QueryFilterBundle\QueryFilter\QueryResult;
use Artprima\QueryFilterBundle\Request\Request;
use Artprima\QueryFilterBundle\Response\Response;
use PHPUnit\Framework\TestCase;
use Tests\Artprima\QueryFilterBundle\Fixtures\Response\ResponseConstructorWithRequiredArguments;
use Tests\Artprima\QueryFilterBundle\Fixtures\Response\ResponseNotImplementingResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class QueryFilterTest extends TestCase
{
    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function constructor_should_throw_no_exceptions_with_proper_argument()
    {
        new QueryFilter(Response::class);
    }

    /**
     * @test
     * @expectedException \Artprima\QueryFilterBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Response class "Tests\Artprima\QueryFilterBundle\Fixtures\Response\ResponseNotImplementingResponseInterface" must implement "Artprima\QueryFilterBundle\Response\ResponseInterface"
     */
    public function constructor_should_throw_exception_for_response_not_implementing_ResponseInterface()
    {
        new QueryFilter(ResponseNotImplementingResponseInterface::class);
    }

    /**
     * @test
     * @expectedException \Artprima\QueryFilterBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Response class "Tests\Artprima\QueryFilterBundle\Fixtures\Response\ResponseConstructorWithRequiredArguments" must have a constructor without required parameters
     */
    public function constructor_should_throw_exception_for_response_constructor_having_required_arguments()
    {
        new QueryFilter(ResponseConstructorWithRequiredArguments::class);
    }

    public function testGetDataSimpleBaseCase()
    {
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'limit' => 100,
            'page'=> 3,
            'filter' => [
                'c.dummy' => 'the road to hell',
            ],
            'sortby' => 'c.id',
            'sortdir' => 'asc',
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['c.dummy']);
        $config->setSortCols(['c.id']);
        $config->setAllowedLimits([10, 15, 100]);
        $config->setDefaultLimit(10);

        $config->setRepositoryCallback(function(QueryFilterArgs $args) {
            self::assertSame(100, $args->getLimit());
            self::assertSame(200, $args->getOffset());
            self::assertEquals([
                (new Filter())
                    ->setField('c.dummy')
                    ->setType('like')
                    ->setX('the road to hell'),
            ], $args->getSearchBy());
            self::assertEquals([
                'c.id' => 'asc',
            ], $args->getSortBy());

            return new QueryResult([
                ["dummy"],
                ["wammy"],
            ], 1000);
        });
        $response = $queryFilter->getData($config);
        self::assertEquals([
            ["dummy"],
            ["wammy"],
        ], $response->getData());
        self::assertSame(1000, $response->getMeta()['total_records']);
    }

    /**
     * @test
     * @expectedException \Artprima\QueryFilterBundle\Exception\UnexpectedValueException
     */
    public function testGetDataInvalidFilter()
    {
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'filter' => [
                'c.unknownColumn' => 'the road to hell',
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['c.dummy']);
        $config->setStrictColumns(true);

        $response = $queryFilter->getData($config);
        $response->getData();
    }

    /**
     * @test
     * @expectedException \Artprima\QueryFilterBundle\Exception\UnexpectedValueException
     */
    public function testGetDataInvalidSortColumn()
    {
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'sortby' => 'c.invalidColumn',
            'sortdir' => 'asc',
        ]));
        $config->setRequest($request);
        $config->setSortCols(['c.id']);
        $config->setStrictColumns(true);

        $response = $queryFilter->getData($config);
        $response->getData();
    }

    public function testGetDataAdvancedBaseCase()
    {
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'limit' => 100,
            'page'=> 3,
            'filter' => [
                [
                    'field' => 'c.hell',
                    'type' => 'eq',
                    'x' => 'the road to hell',
                ],
                [
                    'field' => 'c.heaven',
                    'type' => 'like',
                    'x' => 'the road to heaven',
                ],
                [
                    'field' => 'c.latency',
                    'type' => 'between',
                    'x' => '10',
                    'y' => '100',
                ],
                [
                    'field' => 'c.hell',
                    'type' => 'like',
                    'x' => 'the road to hell',
                    'connector' => 'or',
                    'extra' => 'exact',
                ],
            ],
            'sortby' => 'c.id',
            'sortdir' => 'asc',
            'simple' => '0',
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['c.hell', 'c.heaven', 'c.latency']);
        $config->setSortCols(['c.id']);
        $config->setAllowedLimits([10, 15, 100]);
        $config->setDefaultLimit(10);

        $config->setRepositoryCallback(function(QueryFilterArgs $args) {
            self::assertSame(100, $args->getLimit());
            self::assertSame(200, $args->getOffset());
            self::assertEquals([
                (new Filter())
                    ->setField('c.hell')
                    ->setType('eq')
                    ->setX('the road to hell'),
                (new Filter())
                    ->setField('c.heaven')
                    ->setType('like')
                    ->setX('the road to heaven'),
                (new Filter())
                    ->setField('c.latency')
                    ->setType('between')
                    ->setX('10')
                    ->setY('100'),
                (new Filter())
                    ->setField('c.hell')
                    ->setType('like')
                    ->setX('the road to hell')
                    ->setExtra('exact')
                    ->setConnector('or')
            ], $args->getSearchBy());
            self::assertEquals([
                'c.id' => 'asc',
            ], $args->getSortBy());

            return new QueryResult([
                ["dummy"],
                ["wammy"],
            ], 1000);
        });
        $response = $queryFilter->getData($config);
        self::assertEquals([
            ["dummy"],
            ["wammy"],
        ], $response->getData());
        self::assertSame(1000, $response->getMeta()['total_records']);
    }

    public function testAliases()
    {
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'limit' => 100,
            'page'=> 3,
            'filter' => [
                [
                    'field' => 'fullname',
                    'type' => 'eq',
                    'x' => 'Vassily Poupkine',
                    'having' => '1',
                ],
                [
                    'field' => 'c.heaven',
                    'type' => 'like',
                    'x' => 'the road to heaven',
                ],
            ],
            'sortby' => 'c.id',
            'sortdir' => 'asc',
            'simple' => '0',
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['fullname', 'c.heaven']);
        $config->setSortCols(['c.id']);
        $config->setAllowedLimits([10, 15, 100]);
        $config->setDefaultLimit(10);
        $config->setSearchByAliases([
            (new Alias('fullname', 'concat(\'c.firstname, \' \', c.lastname\')'))
        ]);

        $config->setRepositoryCallback(function(QueryFilterArgs $args) {
            self::assertSame(100, $args->getLimit());
            self::assertSame(200, $args->getOffset());
            self::assertEquals([
                (new Filter())
                    ->setField('concat(\'c.firstname, \' \', c.lastname\')')
                    ->setType('eq')
                    ->setX('Vassily Poupkine')
                    ->setHaving(true),
                (new Filter())
                    ->setField('c.heaven')
                    ->setType('like')
                    ->setX('the road to heaven'),
            ], $args->getSearchBy());
            self::assertEquals([
                'c.id' => 'asc',
            ], $args->getSortBy());

            return new QueryResult([
                ["dummy"],
                ["wammy"],
            ], 1000);
        });
        $response = $queryFilter->getData($config);
        self::assertEquals([
            ["dummy"],
            ["wammy"],
        ], $response->getData());
        self::assertSame(1000, $response->getMeta()['total_records']);
    }
}
