<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\DependencyInjection;

use Artprima\QueryFilterBundle\DependencyInjection\ArtprimaQueryFilterExtension;
use Artprima\QueryFilterBundle\Query\ConditionManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ArtprimaQueryFilterExtensionTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class ArtprimaQueryFilterExtensionTest extends TestCase
{
    public function testDisabledConditionsType()
    {
        $container = new ContainerBuilder();

        $extension = new ArtprimaQueryFilterExtension();
        $config = [];

        $extension->load([$config], $container);

        self::assertIsArray($container->getParameter('query_filter_bundle.disabled_conditions'));
    }

    public function testAssertProxyQueryBuilderManagerService()
    {
        $container = new ContainerBuilder();

        $extension = new ArtprimaQueryFilterExtension();
        $config = [];

        $extension->load([$config], $container);

        self::assertTrue($container->hasDefinition(ConditionManager::class));
    }

    public function testAssertCondition()
    {
        $container = new ContainerBuilder();

        $extension = new ArtprimaQueryFilterExtension();
        $config = [];

        $extension->load([$config], $container);

        $tagsByServiceId = $container->findTaggedServiceIds('proxy_query_builder.condition');

        foreach ($tagsByServiceId as $serviceId => $tags) {
            foreach ($tags as $tag) {
                self::assertArrayHasKey('condition', $tag);
            }
        }
    }
}
