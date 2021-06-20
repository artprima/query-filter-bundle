<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\EventListener;

use Artprima\QueryFilterBundle\Controller\Annotations\QueryFilter;
use Artprima\QueryFilterBundle\EventListener\QueryFilterListener;
use Artprima\QueryFilterBundle\QueryFilter\Config\BaseConfig;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilterArgs;
use Artprima\QueryFilterBundle\QueryFilter\QueryResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class QueryFilterListenerTest extends TestCase
{
    public function testOnKernelView()
    {
        $request = new \Artprima\QueryFilterBundle\QueryFilter\Request(new Request());
        $queryResult = new QueryResult([
            'value',
        ], 1);

        $config = new BaseConfig();
        $config->setRequest($request);
        $config->setRepositoryCallback(function (QueryFilterArgs $args) use ($queryResult): QueryResult {
            return $queryResult;
        });

        $event = $this->getViewEvent($config, $this->createRequest(
            new QueryFilter([])
        ));

        $listener = new QueryFilterListener();
        $listener->onKernelView($event);

        $response = $event->getControllerResult();
        self::assertEquals(['value'], $response->getData());
        self::assertEquals(1, $response->getMeta()['total_records']);
    }

    /**
     * @test
     */
    public function onKernelViewShouldDoNothingOnQueryfilterAttributeNotSet()
    {
        $config = new BaseConfig();

        $event = $this->getViewEvent($config, $this->createRequest());

        $listener = new QueryFilterListener();
        $listener->onKernelView($event);

        self::assertEquals($config, $event->getControllerResult());
    }

    /**
     * @test
     */
    public function onKernelViewShouldDoNothingOnControllerResultNotInstanceOfConfigInterface()
    {
        $config = 'idiocracy';
        $event = $this->getViewEvent($config, $this->createRequest());
        $listener = new QueryFilterListener();
        $listener->onKernelView($event);

        self::assertEquals($config, $event->getControllerResult());
    }

    private function getViewEvent($config, Request $request): ViewEvent
    {
        $mockKernel = $this->getMockForAbstractClass('Symfony\Component\HttpKernel\Kernel', ['test', '']);

        return new ViewEvent($mockKernel, $request, HttpKernelInterface::MAIN_REQUEST, $config);
    }

    private function createRequest($queryFilterAnnotation = null): Request
    {
        return new Request([], [], [
            '_queryfilter' => $queryFilterAnnotation,
        ]);
    }
}
