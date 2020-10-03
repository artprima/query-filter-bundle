<?php declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\QueryFilter;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Artprima\QueryFilterBundle\Exception\MissingArgumentException;
use Artprima\QueryFilterBundle\Exception\UnexpectedValueException;
use Artprima\QueryFilterBundle\Query\Filter;
use Artprima\QueryFilterBundle\QueryFilter\Config\Alias;
use Artprima\QueryFilterBundle\QueryFilter\Config\BaseConfig;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilter;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilterArgs;
use Artprima\QueryFilterBundle\QueryFilter\QueryResult;
use Artprima\QueryFilterBundle\Request\Request;
use Artprima\QueryFilterBundle\Response\Response;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Artprima\QueryFilterBundle\Fixtures\Response\ResponseConstructorWithRequiredArguments;
use Tests\Unit\Artprima\QueryFilterBundle\Fixtures\Response\ResponseNotImplementingResponseInterface;
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
     */
    public function constructor_should_throw_exception_for_response_not_implementing_ResponseInterface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Response class \"Tests\Unit\Artprima\QueryFilterBundle\Fixtures\Response\ResponseNotImplementingResponseInterface\" must implement \"Artprima\QueryFilterBundle\Response\ResponseInterface\""
        );
        new QueryFilter(ResponseNotImplementingResponseInterface::class);
    }

    /**
     * @test
     */
    public function constructor_should_throw_exception_for_response_constructor_having_required_arguments()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Response class \"Tests\Unit\Artprima\QueryFilterBundle\Fixtures\Response\ResponseConstructorWithRequiredArguments\" must have a constructor without required parameters"
        );
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

    public function testGetDataSimpleBaseCaseNoSortColSet()
    {
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'limit' => 100,
            'page'=> 3,
            'filter' => [
                'c.dummy' => 'the road to hell',
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['c.dummy']);
        $config->setSortCols(['c.id'], ['c.id' => 'asc']);
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

    public function testGetDataInvalidSimpleFilterWithThrow()
    {
        self::expectException(UnexpectedValueException::class);
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'filter' => [
                'c.knownColumn' => 'shla sasha po shosse i sosala sushku',
                'c.unknownColumn' => 'the road to hell',
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['c.knownColumn']);
        $config->setStrictColumns(true);

        $queryFilter->getData($config);
    }

    public function testGetDataInvalidFullFilterWithThrow()
    {
        self::expectException(UnexpectedValueException::class);
        self::expectExceptionMessage('Invalid filter column requested t.unknownColumn');
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku'
                ],
                [
                    'field' => 't.unknownColumn',
                    'type' => 'eq',
                    'x' => 'the road to hell'
                ]
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['t.knownColumn']);
        $config->setStrictColumns(true);

        $queryFilter->getData($config);
    }

    public function testGetDataInvalidFullFilterFormatWithThrow()
    {
        self::expectException(UnexpectedValueException::class);
        self::expectExceptionMessage('Invalid filter column requested [1]');
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku'
                ],
                't.unknownColumn',
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['t.knownColumn']);
        $config->setStrictColumns(true);

        $queryFilter->getData($config);
    }

    public function testGetDataInvalidSimpleFilterWithoutThrow()
    {
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'filter' => [
                't.knownColumn' => 'shla sasha po shosse i sosala sushku',
                't.unknownColumn' => 'the road to hell', // will be ignored
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['t.knownColumn']);
        $config->setStrictColumns(false);

        $config->setRepositoryCallback(function(QueryFilterArgs $args) {
            self::assertEquals([
                (new Filter())
                    ->setField('t.knownColumn')
                    ->setType('like')
                    ->setX('shla sasha po shosse i sosala sushku'),
            ], $args->getSearchBy());
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
    }

    public function testGetDataInvalidFullFilterWithoutThrow()
    {
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku'
                ],
                [ // will be ignored
                    'field' => 't.unknownColumn',
                    'type' => 'eq',
                    'x' => 'the road to hell'
                ]
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['t.knownColumn']);
        $config->setStrictColumns(false);

        $config->setRepositoryCallback(function(QueryFilterArgs $args) {
            self::assertEquals([
                (new Filter())
                    ->setField('t.knownColumn')
                    ->setType('eq')
                    ->setX('shla sasha po shosse i sosala sushku'),
            ], $args->getSearchBy());
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
    }

    public function testGetDataInvalidFullFilterFormatWithoutThrow()
    {
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku'
                ],
                't.unknownColumn', // will be ignored
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['t.knownColumn']);
        $config->setStrictColumns(false);

        $config->setRepositoryCallback(function(QueryFilterArgs $args) {
            self::assertEquals([
                (new Filter())
                    ->setField('t.knownColumn')
                    ->setType('eq')
                    ->setX('shla sasha po shosse i sosala sushku'),
            ], $args->getSearchBy());
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
    }

    public function testGetDataInvalidRepositoryCallback()
    {
        self::expectException(MissingArgumentException::class);
        self::expectExceptionMessage('Repository callback is not set');
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku'
                ],
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedCols(['t.knownColumn']);
        $queryFilter->getData($config);
    }

    public function testGetDataInvalidSortColumn()
    {
        self::expectException(UnexpectedValueException::class);
        self::expectExceptionMessage('Invalid sort column requested c.invalidColumn');
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'sortby' => 'c.invalidColumn',
            'sortdir' => 'asc',
        ]));
        $config->setRequest($request);
        $config->setSortCols(['c.id']);
        $config->setStrictColumns(true);

        $queryFilter->getData($config);
    }

    public function testGetDataInvalidSortColumnType()
    {
        self::expectException(UnexpectedValueException::class);
        self::expectExceptionMessage('Invalid sort type requested to_the_left');
        $queryFilter = new QueryFilter(Response::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'sortby' => 'c.id',
            'sortdir' => 'to_the_left',
        ]));
        $config->setRequest($request);
        $config->setSortCols(['c.id']);
        $config->setStrictColumns(true);

        $queryFilter->getData($config);
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
