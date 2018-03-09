<?php declare(strict_types = 1);

namespace Tests\Artprima\QueryFilterBundle\Query;

use Artprima\QueryFilterBundle\Query\Condition\ConditionInterface;
use Artprima\QueryFilterBundle\Query\ConditionManager;
use PHPUnit\Framework\TestCase;

/**
 * Class ConditionManagerTest
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Tests\Artprima\QueryFilterBundle\Query
 */
class ConditionManagerTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testWrapQueryBuilder()
    {
        $manager = new ConditionManager();

        $condition = self::getMockBuilder(ConditionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->add($condition, 'dummy');
        self::assertSame($condition, $manager->offsetGet('dummy'));
    }
}