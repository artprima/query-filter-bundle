<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\Query\Condition;

use Artprima\QueryFilterBundle\Query\Condition\IsNotNull;
use Artprima\QueryFilterBundle\Query\Filter;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class IsNotNullTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class IsNotNullTest extends TestCase
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
            ->expects(self::never())
            ->method('setParameter');

        $condition = new IsNotNull();

        $expr = $condition->getExpr($qb, 0, (new Filter())
            ->setField('t.dummy')
        );

        self::assertSame('t.dummy IS NOT NULL', (string) $expr);
    }
}
