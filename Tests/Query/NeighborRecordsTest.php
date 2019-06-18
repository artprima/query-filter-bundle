<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Tests\Query;

use Artprima\QueryFilterBundle\Query\NeighborRecords;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

/**
 * Class NeighborRecordsTest
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Tests\Query
 */
class NeighborRecordsTest extends TestCase
{
    public function testGetQueryBuilderFilteredByNeighborRecord()
    {
        $manager = self::getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $instance = new NeighborRecords('Dummy', $manager);
        $qb = $instance->getQueryBuilderFilteredByNeighborRecord(1, false);
        self::assertEquals('SELECT c.id FROM Dummy c WHERE c.id > :id ORDER BY c.id ASC', $qb->getDQL());
        $qb = $instance->getQueryBuilderFilteredByNeighborRecord(1, true);
        self::assertEquals('SELECT c.id FROM Dummy c WHERE c.id < :id ORDER BY c.id DESC', $qb->getDQL());
    }
}