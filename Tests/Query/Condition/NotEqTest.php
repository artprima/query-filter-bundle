<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Tests\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\NotEq;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class NotEqTest
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Tests\Query\Condition
 */
class NotEqTest extends TestCase
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
            ->with(0, 10)
            ->willReturn($qb);

        $condition = new NotEq();

        $expr = $condition->getExpr($qb, 't.dummy', 0, ['val' => 10]);

        self::assertSame('t.dummy <> ?0', (string)$expr);
    }
}