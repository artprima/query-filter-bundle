<?php declare(strict_types = 1);

namespace Artprima\QueryFiMemberOferBundle\Tests\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\MemberOf;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class MemberOfTest
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Tests\Query\Condition
 */
class MemberOfTest extends TestCase
{
    public function testGetExpr()
    {
        $qb = self::getMockBuilder(QueryBuilder::class)
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

        $expr = $condition->getExpr($qb, 't.dummy', 0, ['val' => '1']);

        self::assertSame('?0 MEMBER OF t.dummy', (string)$expr);
    }

    public function testGetExprMultipleValues()
    {
        $qb = self::getMockBuilder(QueryBuilder::class)
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

        $expr = $condition->getExpr($qb, 't.dummy', 0, ['val' => '1,2,3,4,5']);

        self::assertSame('?0 MEMBER OF t.dummy', (string)$expr);
    }
}