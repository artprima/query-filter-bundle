<?php

/*
 * This file is part of the Catalog Management API package.
 *
 * (c) Feedo <vyvoj@feedo.cz>
 * (c) Denis Voytyuk <denis@voituk.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Artprima\QueryFilterBundle\EventListener;

use Artprima\QueryFilterBundle\QueryFilter\Config\ConfigInterface;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilter;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Class QueryFilterListener
 *
 * @author Denis Voytyuk <denis@voituk.ru>
 *
 * @package AppBundle\EventListener
 */
class QueryFilterListener
{
    /**
     * @var QueryFilter
     */
    private $queryFilter;

    public function __construct(QueryFilter $queryFilter)
    {
        $this->queryFilter = $queryFilter;
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        /** @var QueryFilter $configuration */
        $configuration = $request->attributes->get('_queryfilter');

        if (!$configuration) {
            return;
        }

        $config = $event->getControllerResult();

        if (!$config instanceof ConfigInterface) {
            return;
        }

        $result = $this->queryFilter->getData($config);
        $event->setControllerResult($result);
    }
}
