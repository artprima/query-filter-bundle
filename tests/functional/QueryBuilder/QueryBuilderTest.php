<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\tests\functional\QueryBuilder;

use Artprima\QueryFilterBundle\ArtprimaQueryFilterBundle;
use Artprima\QueryFilterBundle\DependencyInjection\ArtprimaQueryFilterExtension;
use Artprima\QueryFilterBundle\ParamConverter\ConfigConverter;
use Artprima\QueryFilterBundle\Query\Condition;
use Artprima\QueryFilterBundle\Query\ConditionManager;
use Artprima\QueryFilterBundle\tests\functional\Test\MakePublicCompilerPass;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class QueryBuilderTest extends TestCase
{
    private static $conditions = [
        'between' => Condition\Between::class,
        'eq' => Condition\Eq::class,
        'gt' => Condition\Gt::class,
        'gte' => Condition\Gte::class,
        'in' => Condition\In::class,
        'is not null' => Condition\IsNotNull::class,
        'is null' => Condition\IsNull::class,
        'like' => Condition\Like::class,
        'lt' => Condition\Lt::class,
        'lte' => Condition\Lte::class,
        'member of' => Condition\MemberOf::class,
        'not between' => Condition\NotBetween::class,
        'not eq' => Condition\NotEq::class,
        'not in' => Condition\NotIn::class,
        'not like' => Condition\NotLike::class,
    ];

    public function testQueryBuilder()
    {
        $container = new ContainerBuilder();

        $bundle = new ArtprimaQueryFilterBundle();
        $bundle->setContainer($container);
        $bundle->build($container);

        // In this step we make the service public for it to be accessible via the container
        // I could have of course used the WebTestCase but it's too heavy for my tasks
        $container->addCompilerPass(new MakePublicCompilerPass(ConditionManager::class));
        $container->addCompilerPass(new MakePublicCompilerPass('query_filter_bundle.param_converter.query_filter_config'));

        $extension = new ArtprimaQueryFilterExtension();
        $container->setParameter('kernel.debug', true);
        $extension->load([], $container);
        $doctrine = new DoctrineExtension();
        $doctrine->load([['dbal' => []]], $container);

        $container->compile();

        // check registered services
        $conditionManager = $container->get(ConditionManager::class);
        self::assertInstanceOf(ConditionManager::class, $conditionManager);
        $queryFilterConfig = $container->get('query_filter_bundle.param_converter.query_filter_config');
        self::assertInstanceOf(ConfigConverter::class, $queryFilterConfig);

        // check registered conditions
        $conditions = $conditionManager->all();
        foreach (self::$conditions as $operator => $class) {
            self::assertArrayHasKey($operator, $conditions);
            self::assertInstanceOf($class, $conditions[$operator]);
        }
    }

    public function testQueryBuilderNoDoctrine()
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('The service "query_filter_bundle.param_converter.query_filter_config" has a dependency on a non-existent service "doctrine"');

        $container = new ContainerBuilder();

        $bundle = new ArtprimaQueryFilterBundle();
        $bundle->setContainer($container);
        $bundle->build($container);

        // In this step we make the service public for it to be accessible via the container
        // I could have of course used the WebTestCase but it's too heavy for my tasks
        $container->addCompilerPass(new MakePublicCompilerPass(ConditionManager::class));
        $container->addCompilerPass(new MakePublicCompilerPass('query_filter_bundle.param_converter.query_filter_config'));

        $extension = new ArtprimaQueryFilterExtension();
        $container->setParameter('kernel.debug', true);
        $extension->load([], $container);

        $container->compile();
    }
}
