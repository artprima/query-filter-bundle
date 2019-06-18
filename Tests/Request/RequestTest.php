<?php declare(strict_types=1);

namespace Tests\Artprima\QueryFilterBundle\Request;

use Artprima\QueryFilterBundle\Request\Request;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    private static $request;

    public static function setUpBeforeClass()
    {
        $httpRequest = new HttpRequest(array(
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
            'simple' => '0',
        ));
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
        $httpRequest = new HttpRequest(array(
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
            'simple' => '0',
        ));
        $request = new Request($httpRequest);
        self::assertSame(1, $request->getPageNum());
    }

    public function testNoLimit()
    {
        $httpRequest = new HttpRequest(array(
            'page' => '42',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
            'simple' => '0',
        ));
        $request = new Request($httpRequest);
        self::assertSame(-1, $request->getLimit());
    }

    public function testDefaultSortDir()
    {
        $httpRequest = new HttpRequest(array(
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'simple' => '0',
        ));
        $request = new Request($httpRequest);
        self::assertSame('asc', $request->getSortDir());
    }

    public function testDefaultSimple()
    {
        $httpRequest = new HttpRequest(array(
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
        ));
        $request = new Request($httpRequest);
        self::assertSame(true, $request->isSimple());
    }

    /**
     * @expectedException \Artprima\QueryFilterBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Query filter must be an array
     */
    public function testInvalidQueryException()
    {
        $httpRequest = new HttpRequest(array(
            'page' => '42',
            'limit' => '4242',
            'filter' => 'string',
            'sortby' => 'sortbydummy',
            'sortdir' => 'desc',
            'simple' => '0',
        ));
        $request = new Request($httpRequest);
    }

    /**
     * @expectedException \Artprima\QueryFilterBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Query sort direction must be one of those: asc or desc
     */
    public function testInvalidSortDirException1()
    {
        $httpRequest = new HttpRequest(array(
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => 'invalid',
            'simple' => '0',
        ));
        $request = new Request($httpRequest);
    }

    /**
     * @expectedException \Artprima\QueryFilterBundle\Exception\InvalidArgumentException
     * @expectedExceptionMessage Query sort direction must be one of those: asc or desc
     */
    public function testInvalidSortDirException2()
    {
        $httpRequest = new HttpRequest(array(
            'page' => '42',
            'limit' => '4242',
            'filter' => ['column' => 'value'],
            'sortby' => 'sortbydummy',
            'sortdir' => array('invalid'),
            'simple' => '0',
        ));
        $request = new Request($httpRequest);
    }
}
