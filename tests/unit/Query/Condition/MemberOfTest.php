<?php

declare(strict_types=1);

namespace Artprima\QueryFiMemberOferBundle\Tests\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\MemberOf;
use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class MemberOfTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class MemberOfTest extends TestCase
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
            ->with(0, ['1'])
            ->willReturn($qb);

        $condition = new MemberOf();

        $expr = $condition->getExpr($qb, 0, (new Filter())
            ->setField('t.dummy')
            ->setX('1')
        );

        self::assertSame('?0 MEMBER OF t.dummy', (string) $expr);
    }

    public function testGetExprMultipleValues()
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

        $condition = new MemberOf();

        $expr = $condition->getExpr($qb, 0, (new Filter())
            ->setField('t.dummy')
            ->setX('1,2,3,4,5')
        );

        self::assertSame('?0 MEMBER OF t.dummy', (string) $expr);
    }
}
