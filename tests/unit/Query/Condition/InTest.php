<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\In;
use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class InTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class InTest extends TestCase
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
            ->expects(self::once())
            ->method('setParameter')
            ->with(0, ['1', '2', '3', '4', '5'])
            ->willReturn($qb);

        $condition = new In();

        $expr = $condition->getExpr($qb, 0, (new Filter())
            ->setField('t.dummy')
            ->setX('1,2,3,4,5')
        );

        self::assertSame('t.dummy IN(?0)', (string) $expr);
    }
}
