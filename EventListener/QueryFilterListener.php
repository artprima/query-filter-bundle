<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\EventListener;

use Artprima\QueryFilterBundle\QueryFilter\Config\ConfigInterface;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilter;
use Symfony\Component\HttpKernel\Event\ViewEvent;

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
     * @param ViewEvent $event
     */
    public function onKernelView(ViewEvent $event)
    {
        $request = $event->getRequest();

        /** @var QueryFilter|null $configuration */
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
