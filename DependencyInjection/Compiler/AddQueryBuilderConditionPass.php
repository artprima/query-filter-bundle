<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddQueryBuilderConditionPass
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\DependencyInjection\Compiler
 */
class AddQueryBuilderConditionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('Artprima\QueryFilterBundle\Query\ProxyQueryBuilderManager')) {
            return;
        }

        $definition = $container->getDefinition('Artprima\QueryFilterBundle\Query\ProxyQueryBuilderManager');
        $disabled = $container->getParameter('query_filter_bundle.disabled_conditions');
        $container->getParameterBag()->remove('query_filter_bundle.disabled_conditions');

        foreach ($container->findTaggedServiceIds('proxy_query_builder.condition') as $id => $converters) {
            foreach ($converters as $converter) {
                $name = isset($converter['condition']) ? $converter['condition'] : null;

                if (null !== $name && in_array($name, $disabled)) {
                    continue;
                }

                $definition->addMethodCall('registerCondition', [new Reference($id)]);
            }
        }
    }
}