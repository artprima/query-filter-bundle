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

namespace Tests\Unit\Artprima\QueryFilterBundle\Response;

use Artprima\QueryFilterBundle\QueryFilter\Response;
use PHPUnit\Framework\TestCase;

/**
 * Class Response.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class ResponseTest extends TestCase
{
    public function testSetMeta()
    {
        $response = new Response();
        self::assertCount(0, $response->getMeta());
        $response->setMeta([
            'metakey1' => 'metavalue1',
            'metakey2' => 'metavalue2',
        ]);
        self::assertCount(2, $response->getMeta());
        self::assertSame('metavalue1', $response->getMeta()['metakey1']);
        self::assertSame('metavalue2', $response->getMeta()['metakey2']);
    }

    public function testAddMeta()
    {
        $response = new Response();
        self::assertCount(0, $response->getMeta());
        $response->addMeta('metakey', 'metavalue');
        self::assertCount(1, $response->getMeta());
        self::assertSame('metavalue', $response->getMeta()['metakey']);
    }

    public function testSetData()
    {
        $response = new Response();
        $response->setData(['dummy' => 'wammy']);
        self::assertEquals(['dummy' => 'wammy'], $response->getData());
    }
}
