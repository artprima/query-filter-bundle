<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\Query;

use Artprima\QueryFilterBundle\Query\Condition;
use Artprima\QueryFilterBundle\Query\ConditionManager;
use Artprima\QueryFilterBundle\Query\Filter;
use Artprima\QueryFilterBundle\Query\ProxyQueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class ProxyQueryBuilderTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class ProxyQueryBuilderTest extends TestCase
{
    private $expressionBuilder;
    private $conditionManager;

    public function setUp(): void
    {
        $this->expressionBuilder = new Query\Expr();
        $expressions = [
            'between' => new Condition\Between(),
            'eq' => new Condition\Eq(),
            'gt' => new Condition\Gt(),
            'gte' => new Condition\Gte(),
            'in' => new Condition\In(),
            'is not null' => new Condition\IsNotNull(),
            'is null' => new Condition\IsNull(),
            'like' => new Condition\Like(),
            'lt' => new Condition\Lt(),
            'lte' => new Condition\Lte(),
            'member of' => new Condition\MemberOf(),
            'not between' => new Condition\NotBetween(),
            'not eq' => new Condition\NotEq(),
            'not in' => new Condition\NotIn(),
            'not like' => new Condition\NotLike(),
        ];
        $this->conditionManager = new ConditionManager();
        foreach ($expressions as $key => $expression) {
            $this->conditionManager->add($expression, $key);
        }
    }

    /**
     * @dataProvider filterDataProvider
     */
    public function testGetSortedAndFilteredQuery($filterBy, $sortBy, $expected)
    {
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em
            ->expects(self::any())
            ->method('getExpressionBuilder')
            ->willReturn($this->expressionBuilder);

        $qb = new QueryBuilder($em);

        $builder = new ProxyQueryBuilder($qb, $this->conditionManager);

        self::assertEquals($qb, $builder->getSortedAndFilteredQueryBuilder($filterBy, $sortBy));
        self::assertEquals($expected, $qb->getDQL());
    }

    /**
     * NOTE: expected results are not valid DQL expressions (as they lack from section),
     *       but for testing purposes we don't need that.
     */
    public function filterDataProvider()
    {
        return [
            // no filters, no sort
            [
                [
                ],
                [
                ],
                'SELECT',
            ],

            // no filters, only sort
            [
                [
                ],
                [
                    't.id' => 'asc',
                ],
                'SELECT ORDER BY t.id ASC',
            ],

            // single
            [
                [
                    (new Filter())
                        ->setField('t.id')
                        ->setType('eq')
                        ->setX('10'),
                ],
                [
                    't.id' => 'asc',
                ],
                'SELECT WHERE t.id = ?1 ORDER BY t.id ASC',
            ],

            // combined
            [
                [
                    (new Filter())
                        ->setField('t.id')
                        ->setType('eq')
                        ->setX('100'),
                    (new Filter())
                        ->setField('t.name')
                        ->setType('like')
                        ->setX('john doe'),
                ],
                [
                    't.id' => 'asc',
                ],
                'SELECT WHERE t.id = ?1 AND t.name LIKE ?2 ORDER BY t.id ASC',
            ],

            // with OR connector
            [
                [
                    (new Filter())
                        ->setField('t.id')
                        ->setType('eq')
                        ->setX('100'),
                    (new Filter())
                        ->setField('t.name')
                        ->setType('like')
                        ->setX('john doe')
                        ->setConnector('or'),
                ],
                [
                    't.id' => 'asc',
                ],
                'SELECT WHERE t.id = ?1 OR t.name LIKE ?2 ORDER BY t.id ASC',
            ],

            // with OR connector
            [
                [
                    (new Filter())
                        ->setField('t.id')
                        ->setType('eq')
                        ->setX('100'),
                    (new Filter())
                        ->setField('t.name')
                        ->setType('like')
                        ->setX('john doe')
                        ->setConnector('or'),
                ],
                [
                    't.id' => 'asc',
                ],
                'SELECT WHERE t.id = ?1 OR t.name LIKE ?2 ORDER BY t.id ASC',
            ],

            // having
            [
                [
                    (new Filter())
                        ->setField('t.id')
                        ->setType('eq')
                        ->setX('100')
                        ->setHaving(true),
                ],
                [
                    't.id' => 'asc',
                ],
                'SELECT HAVING t.id = ?1 ORDER BY t.id ASC',
            ],

            // where AND having
            [
                [
                    (new Filter())
                        ->setField('t.id')
                        ->setType('eq')
                        ->setX('100')
                        ->setHaving(true),
                    (new Filter())
                        ->setField('t.name')
                        ->setType('like')
                        ->setX('john doe')
                        ->setConnector('or'),
                ],
                [
                    't.id' => 'asc',
                ],
                'SELECT WHERE t.name LIKE ?2 HAVING t.id = ?1 ORDER BY t.id ASC',
            ],
        ];
    }
}
