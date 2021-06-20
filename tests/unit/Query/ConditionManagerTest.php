<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\Query;

use Artprima\QueryFilterBundle\Query\Condition\ConditionInterface;
use Artprima\QueryFilterBundle\Query\ConditionManager;
use Artprima\QueryFilterBundle\Query\ProxyQueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class ConditionManagerTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class ConditionManagerTest extends TestCase
{
    public function testWrapQueryBuilder()
    {
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = new ConditionManager();
        $pqb = $manager->wrapQueryBuilder(new QueryBuilder($em));
        self::assertInstanceOf(ProxyQueryBuilder::class, $pqb);
    }

    public function testAdd()
    {
        $condition = $this->getMockBuilder(ConditionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = new ConditionManager();
        $manager->add($condition, 'dummy');
        self::assertSame($condition, $manager['dummy']);
    }

    public function testArrayFuncitonality()
    {
        $conditions = [];
        $conditions['dummy1'] = $this->getMockBuilder(ConditionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $conditions['dummy2'] = $this->getMockBuilder(ConditionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager = new ConditionManager();

        $manager['dummy1'] = $conditions['dummy1'];
        $manager['dummy2'] = $conditions['dummy2'];
        self::assertTrue(isset($manager['dummy1']));
        self::assertEquals($manager['dummy1'], $conditions['dummy1']);
        self::assertCount(2, $manager);

        foreach ($manager as $id => $item) {
            self::assertSame($conditions[$id], $item);
        }

        unset($manager['dummy1']);
        self::assertSame($conditions['dummy2'], $item);
        self::assertFalse(isset($manager['dummy1']));
        self::assertCount(1, $manager->all());
    }
}
