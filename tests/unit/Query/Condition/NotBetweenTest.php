<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\NotBetween;
use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class NotBetweenTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class NotBetweenTest extends TestCase
{
    public function testGetExpr()
    {
        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $qb
            ->expects(self::never())
            ->method('expr');

        $qb
            ->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['x0', 1], ['y0', 10])
            ->willReturn($qb);

        $condition = new NotBetween();

        $expr = $condition->getExpr($qb, 0, (new Filter())
            ->setField('t.dummy')
            ->setX('1')
            ->setY('10')
        );

        self::assertSame('t.dummy NOT BETWEEN :x0 AND :y0', (string) $expr);
    }
}
