<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\QueryFilter;

use Artprima\QueryFilterBundle\Exception\MissingArgumentException;
use Artprima\QueryFilterBundle\Exception\UnexpectedValueException;
use Artprima\QueryFilterBundle\Query\Filter;
use Artprima\QueryFilterBundle\QueryFilter\Config\Alias;
use Artprima\QueryFilterBundle\QueryFilter\Config\BaseConfig;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilter;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilterArgs;
use Artprima\QueryFilterBundle\QueryFilter\QueryResult;
use Artprima\QueryFilterBundle\QueryFilter\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class QueryFilterTest extends TestCase
{
    public function testGetDataSimpleBaseCase()
    {
        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'limit' => 100,
            'page' => 3,
            'filter' => [
                'c.dummy' => 'the road to hell',
            ],
            'sortby' => 'c.id',
            'sortdir' => 'asc',
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedFields(['c.dummy']);
        $config->setSortFields(['c.id']);
        $config->setAllowedLimits([10, 15, 100]);
        $config->setDefaultLimit(10);

        $config->setRepositoryCallback(function (QueryFilterArgs $args) {
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
                ['dummy'],
                ['wammy'],
            ], 1000);
        });

        $queryFilter = new QueryFilter($config);
        $response = $queryFilter->getData();
        self::assertEquals([
            ['dummy'],
            ['wammy'],
        ], $response->getData());
        self::assertSame(1000, $response->getMeta()['total_records']);
    }

    public function testGetDataSimpleBaseCaseNoSortColSet()
    {
        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'limit' => 100,
            'page' => 3,
            'filter' => [
                'c.dummy' => 'the road to hell',
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedFields(['c.dummy']);
        $config->setSortFields(['c.id']);
        $config->setSortDefaults(['c.id' => 'asc']);
        $config->setAllowedLimits([10, 15, 100]);
        $config->setDefaultLimit(10);

        $config->setRepositoryCallback(function (QueryFilterArgs $args) {
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
                ['dummy'],
                ['wammy'],
            ], 1000);
        });
        $queryFilter = new QueryFilter($config);
        $response = $queryFilter->getData();
        self::assertEquals([
            ['dummy'],
            ['wammy'],
        ], $response->getData());
        self::assertSame(1000, $response->getMeta()['total_records']);
    }

    public function testGetDataInvalidSimpleFilterWithThrow()
    {
        $this->expectException(UnexpectedValueException::class);

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'filter' => [
                'c.knownColumn' => 'shla sasha po shosse i sosala sushku',
                'c.unknownColumn' => 'the road to hell',
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedFields(['c.knownColumn']);
        $config->setStrictColumns(true);
        $queryFilter = new QueryFilter($config);

        $queryFilter->getData($config);
    }

    public function testGetDataInvalidFullFilterWithThrow()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid filter column requested t.unknownColumn');

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku',
                ],
                [
                    'field' => 't.unknownColumn',
                    'type' => 'eq',
                    'x' => 'the road to hell',
                ],
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedFields(['t.knownColumn']);
        $config->setStrictColumns(true);

        $queryFilter = new QueryFilter($config);
        $queryFilter->getData($config);
    }

    public function testGetDataInvalidFullFilterFormatWithThrow()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid filter column requested [1]');

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku',
                ],
                't.unknownColumn',
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedFields(['t.knownColumn']);
        $config->setStrictColumns(true);

        $queryFilter = new QueryFilter($config);
        $queryFilter->getData($config);
    }

    public function testGetDataInvalidSimpleFilterWithoutThrow()
    {
        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'filter' => [
                't.knownColumn' => 'shla sasha po shosse i sosala sushku',
                't.unknownColumn' => 'the road to hell', // will be ignored
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedFields(['t.knownColumn']);
        $config->setStrictColumns(false);

        $config->setRepositoryCallback(function (QueryFilterArgs $args) {
            self::assertEquals([
                (new Filter())
                    ->setField('t.knownColumn')
                    ->setType('like')
                    ->setX('shla sasha po shosse i sosala sushku'),
            ], $args->getSearchBy());

            return new QueryResult([
                ['dummy'],
                ['wammy'],
            ], 1000);
        });
        $queryFilter = new QueryFilter($config);
        $response = $queryFilter->getData();
        self::assertEquals([
            ['dummy'],
            ['wammy'],
        ], $response->getData());
    }

    public function testGetDataInvalidFullFilterWithoutThrow()
    {
        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku',
                ],

                // will be ignored
                [
                    'field' => 't.unknownColumn',
                    'type' => 'eq',
                    'x' => 'the road to hell',
                ],
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedFields(['t.knownColumn']);
        $config->setStrictColumns(false);

        $config->setRepositoryCallback(function (QueryFilterArgs $args) {
            self::assertEquals([
                (new Filter())
                    ->setField('t.knownColumn')
                    ->setType('eq')
                    ->setX('shla sasha po shosse i sosala sushku'),
            ], $args->getSearchBy());

            return new QueryResult([
                ['dummy'],
                ['wammy'],
            ], 1000);
        });
        $queryFilter = new QueryFilter($config);
        $response = $queryFilter->getData();
        self::assertEquals([
            ['dummy'],
            ['wammy'],
        ], $response->getData());
    }

    public function testGetDataInvalidFullFilterFormatWithoutThrow()
    {
        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku',
                ],
                't.unknownColumn', // will be ignored
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedFields(['t.knownColumn']);
        $config->setStrictColumns(false);

        $config->setRepositoryCallback(function (QueryFilterArgs $args) {
            self::assertEquals([
                (new Filter())
                    ->setField('t.knownColumn')
                    ->setType('eq')
                    ->setX('shla sasha po shosse i sosala sushku'),
            ], $args->getSearchBy());

            return new QueryResult([
                ['dummy'],
                ['wammy'],
            ], 1000);
        });
        $queryFilter = new QueryFilter($config);
        $response = $queryFilter->getData();
        self::assertEquals([
            ['dummy'],
            ['wammy'],
        ], $response->getData());
    }

    public function testGetDataInvalidRepositoryCallback()
    {
        $this->expectException(MissingArgumentException::class);
        $this->expectExceptionMessage('Repository callback is not set');

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'simple' => 0,
            'filter' => [
                [
                    'field' => 't.knownColumn',
                    'type' => 'eq',
                    'x' => 'shla sasha po shosse i sosala sushku',
                ],
            ],
        ]));
        $config->setRequest($request);
        $config->setSearchAllowedFields(['t.knownColumn']);
        $queryFilter = new QueryFilter($config);
        $queryFilter->getData();
    }

    public function testGetDataInvalidSortColumn()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid sort column requested c.invalidColumn');

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'sortby' => 'c.invalidColumn',
            'sortdir' => 'asc',
        ]));
        $config->setRequest($request);
        $config->setSortFields(['c.id']);
        $config->setStrictColumns(true);

        $queryFilter = new QueryFilter($config);
        $queryFilter->getData();
    }

    public function testGetDataInvalidSortColumnType()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid sort type requested to_the_left');

        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'sortby' => 'c.id',
            'sortdir' => 'to_the_left',
        ]));
        $config->setRequest($request);
        $config->setSortFields(['c.id']);
        $config->setStrictColumns(true);

        $queryFilter = new QueryFilter($config);
        $queryFilter->getData();
    }

    public function testGetDataAdvancedBaseCase()
    {
        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'limit' => 100,
            'page' => 3,
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
        $config->setSearchAllowedFields(['c.hell', 'c.heaven', 'c.latency']);
        $config->setSortFields(['c.id']);
        $config->setAllowedLimits([10, 15, 100]);
        $config->setDefaultLimit(10);

        $config->setRepositoryCallback(function (QueryFilterArgs $args) {
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
                    ->setConnector('or'),
            ], $args->getSearchBy());
            self::assertEquals([
                'c.id' => 'asc',
            ], $args->getSortBy());

            return new QueryResult([
                ['dummy'],
                ['wammy'],
            ], 1000);
        });
        $queryFilter = new QueryFilter($config);
        $response = $queryFilter->getData();
        self::assertEquals([
            ['dummy'],
            ['wammy'],
        ], $response->getData());
        self::assertSame(1000, $response->getMeta()['total_records']);
    }

    public function testAliases()
    {
        $config = new BaseConfig();
        $request = new Request(new HttpRequest([
            'limit' => 100,
            'page' => 3,
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
        $config->setSearchAllowedFields(['fullname', 'c.heaven']);
        $config->setSortFields(['c.id']);
        $config->setAllowedLimits([10, 15, 100]);
        $config->setDefaultLimit(10);
        $config->setSearchAliases([
            (new Alias('fullname', 'concat(\'c.firstname, \' \', c.lastname\')')),
        ]);

        $config->setRepositoryCallback(function (QueryFilterArgs $args) {
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
                ['dummy'],
                ['wammy'],
            ], 1000);
        });
        $queryFilter = new QueryFilter($config);
        $response = $queryFilter->getData();
        self::assertEquals([
            ['dummy'],
            ['wammy'],
        ], $response->getData());
        self::assertSame(1000, $response->getMeta()['total_records']);
    }
}
