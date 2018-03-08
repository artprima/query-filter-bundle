<?php

declare(strict_types=1);

namespace Tests\Artprima\QueryFilterBundle\Request;

use Artprima\QueryFilterBundle\EventListener\QueryFilterListener;
use Artprima\QueryFilterBundle\QueryFilter\Config\ConfigInterface;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilter;
use Artprima\QueryFilterBundle\Response\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class QueryFilterListenerTest extends TestCase
{
    public function testOnKernelView()
    {
        $response = self::getMockBuilder(ResponseInterface::class)
            ->getMock();

        $config = self::getMockBuilder(ConfigInterface::class)
            ->getMock();

        $queryFilter = self::getMockBuilder(QueryFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryFilter
            ->expects(self::once())
            ->method('getData')
            ->with($config)
            ->willReturn($response);

        $event = self::getMockBuilder(GetResponseForControllerResultEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        $request->attributes->set('_queryfilter', true);
        $event
            ->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $event
            ->expects(self::once())
            ->method('getControllerResult')
            ->willReturn($config);

        $event
            ->expects(self::once())
            ->method('setControllerResult')
            ->with($response);

        $listener = new QueryFilterListener($queryFilter);
        $listener->onKernelView($event);
    }
}