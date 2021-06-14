<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\DependencyInjection\Compiler;

use Artprima\QueryFilterBundle\Query\ConditionManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddQueryBuilderConditionPass.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class AddQueryBuilderConditionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition(ConditionManager::class)) {
            return;
        }

        $definition = $container->getDefinition(ConditionManager::class);
        $disabled = $container->getParameter('query_filter_bundle.disabled_conditions');
        $container->getParameterBag()->remove('query_filter_bundle.disabled_conditions');

        foreach ($container->findTaggedServiceIds('proxy_query_builder.condition') as $id => $tags) {
            foreach ($tags as $tag) {
                $name = $tag['condition'] ?? null;

                if (null !== $name && in_array($name, $disabled)) {
                    continue;
                }

                $definition->addMethodCall('add', [new Reference($id), $name]);
            }
        }
    }
}
