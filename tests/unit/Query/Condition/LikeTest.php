<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\Like;
use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class EqTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class LikeTest extends TestCase
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
            ->with(0, '%road%to%hell%')
            ->willReturn($qb);

        $condition = new Like();

        $expr = $condition->getExpr($qb, 0, (new Filter())
            ->setField('t.dummy')
            ->setX('road to hell')
        );

        self::assertSame('t.dummy LIKE ?0', (string) $expr);
    }

    public function testGetExprExact()
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
            ->with(0, '%road to hell%')
            ->willReturn($qb);

        $condition = new Like();

        $expr = $condition->getExpr($qb, 0, (new Filter())
            ->setField('t.dummy')
            ->setX('road to hell')
            ->setExtra('exact')
        );

        self::assertSame('t.dummy LIKE ?0', (string) $expr);
    }
}
