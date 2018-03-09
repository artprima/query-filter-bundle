<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Tests\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\NotBetween;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class NotBetweenTest
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Tests\Query\Condition
 */
class NotBetweenTest extends TestCase
{
    public function testGetExpr()
    {
        $qb = self::getMockBuilder(QueryBuilder::class)
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

        $expr = $condition->getExpr($qb, 't.dummy', 0, ['x' => 1, 'y' => 10]);

        self::assertSame('t.dummy NOT BETWEEN :x0 AND :y0', (string)$expr);
    }
}