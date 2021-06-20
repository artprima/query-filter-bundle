<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\Request;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Artprima\QueryFilterBundle\QueryFilter\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class RequestTest extends TestCase
{
    private static Request $request;

    public static function setUpBeforeClass(): void
    {
        $httpRequest = new HttpRequest([
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
            'simple' => '0',
        ]);
        self::$request = new Request($httpRequest);
    }

    public function testGetPageNum()
    {
        self::assertSame(42, self::$request->getPageNum());
    }

    public function testGetLimit()
    {
        self::assertSame(4242, self::$request->getLimit());
    }

    public function testGetQuery()
    {
        self::assertSame(['column' => 'value'], self::$request->getQuery());
    }

    public function testGetSortBy()
    {
        self::assertSame('sortbydummy', self::$request->getSortBy());
    }

    public function testGetSortDir()
    {
        self::assertSame('desc', self::$request->getSortDir());
    }

    public function testIsSimple()
    {
        self::assertSame(false, self::$request->isSimple());
    }

    public function testDefaultPageNum()
    {
        $httpRequest = new HttpRequest([
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
            'simple' => '0',
        ]);
        $request = new Request($httpRequest);
        self::assertSame(1, $request->getPageNum());
    }

    public function testNoLimit()
    {
        $httpRequest = new HttpRequest([
            'page' => '42',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
            'simple' => '0',
        ]);
        $request = new Request($httpRequest);
        self::assertSame(-1, $request->getLimit());
    }

    public function testDefaultSortDir()
    {
        $httpRequest = new HttpRequest([
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'simple' => '0',
        ]);
        $request = new Request($httpRequest);
        self::assertSame('asc', $request->getSortDir());
    }

    public function testDefaultSimple()
    {
        $httpRequest = new HttpRequest([
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
        ]);
        $request = new Request($httpRequest);
        self::assertSame(true, $request->isSimple());
    }

    public function testInvalidQueryException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query filter must be an array');
        $httpRequest = new HttpRequest([
            'page' => '42',
            'limit' => '4242',
            'filter' => 'string',
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
            'simple' => '0',
        ]);
        $request = new Request($httpRequest);
    }

    /**
     * @doesNotPerformAssertions should not throw exceptions
     */
    public function testInvalidSortDirException1()
    {
        $httpRequest = new HttpRequest([
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'invalid',
            'simple' => '0',
        ]);
        $request = new Request($httpRequest);
    }

    public function testInvalidSortDirException2()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query sort direction must be string or null');
        $httpRequest = new HttpRequest([
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => ['invalid'],
            'simple' => '0',
        ]);
        $request = new Request($httpRequest);
    }
}
