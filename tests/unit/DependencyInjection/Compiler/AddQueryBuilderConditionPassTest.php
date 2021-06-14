<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\DependencyInjection\Compiler;

use Artprima\QueryFilterBundle\DependencyInjection\Compiler\AddQueryBuilderConditionPass;
use Artprima\QueryFilterBundle\Query\ConditionManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AddQueryBuilderConditionPass.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class AddQueryBuilderConditionPassTest extends TestCase
{
    /**
     * @var AddQueryBuilderConditionPass
     */
    private $pass;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $managerDefinition;

    public function setUp(): void
    {
        $this->pass = new AddQueryBuilderConditionPass();
        $this->container = new ContainerBuilder();
        $this->managerDefinition = new Definition();
        $this->container->setDefinition(ConditionManager::class, $this->managerDefinition);
        $this->container->setParameter('query_filter_bundle.disabled_conditions', []);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testProcessNoOpNoManager()
    {
        $this->container->removeDefinition(ConditionManager::class);
        $this->pass->process($this->container);
    }

    public function testProcessNoOpNoTaggedServices()
    {
        $this->pass->process($this->container);
        self::assertCount(0, $this->managerDefinition->getMethodCalls());
    }

    public function testProcessAddsTaggedServices()
    {
        $condition1 = new Definition();
        $condition1->setTags([
            'proxy_query_builder.condition' => [
                [
                    'condition' => 'bar',
                ],
            ],
        ]);

        $condition2 = new Definition();
        $condition2->setTags([
            'proxy_query_builder.condition' => [
                [
                    'condition' => 'baz',
                ],
            ],
        ]);

        $this->container->setDefinition('condition_one', $condition1);
        $this->container->setDefinition('condition_two', $condition2);

        $this->container->setParameter('query_filter_bundle.disabled_conditions', []);

        $this->pass->process($this->container);

        $methodCalls = $this->managerDefinition->getMethodCalls();
        self::assertCount(2, $methodCalls);
        self::assertEquals(['add', [new Reference('condition_one'), 'bar']], $methodCalls[0]);
        self::assertEquals(['add', [new Reference('condition_two'), 'baz']], $methodCalls[1]);
    }

    public function testProcessDisabledTaggedServices()
    {
        $condition1 = new Definition();
        $condition1->setTags([
            'proxy_query_builder.condition' => [
                [
                    'condition' => 'bar',
                ],
            ],
        ]);

        $condition2 = new Definition();
        $condition2->setTags([
            'proxy_query_builder.condition' => [
                [
                    'condition' => 'baz',
                ],
            ],
        ]);

        $condition3 = new Definition();
        $condition3->setTags([
            'proxy_query_builder.condition' => [
                [
                    'condition' => 'foo',
                ],
            ],
        ]);

        $this->container->setDefinition('condition_one', $condition1);
        $this->container->setDefinition('condition_two', $condition2);
        $this->container->setDefinition('condition_three', $condition3);

        $this->container->setParameter('query_filter_bundle.disabled_conditions', ['bar', 'baz']);

        $this->pass->process($this->container);

        $methodCalls = $this->managerDefinition->getMethodCalls();
        self::assertCount(1, $methodCalls);
        self::assertEquals(['add', [new Reference('condition_three'), 'foo']], $methodCalls[0]);
    }
}
