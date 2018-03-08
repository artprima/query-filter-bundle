<?php declare(strict_types = 1);

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

    /**
     * @test
     */
    public function onKernelView_should_do_nothing_on_queryfilter_attribute_not_set()
    {
        $response = self::getMockBuilder(ResponseInterface::class)
            ->getMock();

        $config = self::getMockBuilder(ConfigInterface::class)
            ->getMock();

        $queryFilter = self::getMockBuilder(QueryFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryFilter
            ->expects(self::never())
            ->method('getData')
            ->with($config)
            ->willReturn($response);

        $event = self::getMockBuilder(GetResponseForControllerResultEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        $event
            ->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $event
            ->expects(self::never())
            ->method('getControllerResult')
            ->willReturn($config);

        $event
            ->expects(self::never())
            ->method('setControllerResult')
            ->with($response);

        $listener = new QueryFilterListener($queryFilter);
        $listener->onKernelView($event);
    }

    /**
     * @test
     */
    public function onKernelView_should_do_nothing_on_controller_result_not_instance_of_ConfigInterface()
    {
        $response = self::getMockBuilder(ResponseInterface::class)
            ->getMock();

        $config = 'idiocracy';

        $queryFilter = self::getMockBuilder(QueryFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $queryFilter
            ->expects(self::never())
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
            ->expects(self::never())
            ->method('setControllerResult')
            ->with($response);

        $listener = new QueryFilterListener($queryFilter);
        $listener->onKernelView($event);
    }
}
