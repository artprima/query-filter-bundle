<?php

declare(strict_types=1);

/*
 * This file is part of the QueryFilterBundle package.
 *
 * (c) Denis Voytyuk <ask@artprima.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Unit\Artprima\QueryFilterBundle\Controller\Annotations;

use Artprima\QueryFilterBundle\Controller\Annotations\QueryFilter;
use PHPUnit\Framework\TestCase;

/**
 * Class QueryFilterTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class QueryFilterTest extends TestCase
{
    public function testAliasName()
    {
        $qf = new QueryFilter([]);
        self::assertSame('queryfilter', $qf->getAliasName());
    }
}
