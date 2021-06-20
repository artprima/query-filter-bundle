<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\Between;
use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class BetweenTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class BetweenTest extends TestCase
{
    public function testGetExpr()
    {
        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $qb
            ->expects(self::once())
            ->method('expr')
            ->willReturn(new Expr());

        $qb
            ->expects($this->exactly(2))
            ->method('setParameter')
            ->withConsecutive(['x0', 1], ['y0', 10])
            ->willReturn($qb);

        $condition = new Between();

        $expr = $condition->getExpr($qb, 0, (new Filter())
            ->setField('t.dummy')
            ->setX(1)
            ->setY(10)
        );

        self::assertSame('t.dummy BETWEEN :x0 AND :y0', $expr);
    }
}
