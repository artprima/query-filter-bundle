<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Tests\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\In;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class InTest
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Tests\Query\Condition
 */
class InTest extends TestCase
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
            ->with(0, ['1', '2', '3', '4', '5'])
            ->willReturn($qb);

        $condition = new In();

        $expr = $condition->getExpr($qb, 't.dummy', 0, ['val' => '1,2,3,4,5']);

        self::assertSame('t.dummy IN(?0)', (string)$expr);
    }
}