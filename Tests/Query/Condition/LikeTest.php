<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Tests\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\Like;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class EqTest
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Tests\Query\Condition
 */
class LikeTest extends TestCase
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
            ->with(0, '%road%to%hell%')
            ->willReturn($qb);

        $condition = new Like();

        $expr = $condition->getExpr($qb, 't.dummy', 0, ['val' => 'road to hell']);

        self::assertSame('t.dummy LIKE ?0', (string)$expr);
    }

    public function testGetExprExact()
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
            ->with(0, '%road to hell%')
            ->willReturn($qb);

        $condition = new Like();

        $expr = $condition->getExpr($qb, 't.dummy', 0, ['val' => 'road to hell', 'exact' => '1']);

        self::assertSame('t.dummy LIKE ?0', (string)$expr);
    }
}