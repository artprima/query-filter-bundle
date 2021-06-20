<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\DependencyInjection;

use Artprima\QueryFilterBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigurationTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class ConfigurationTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        self::assertSame('artprima_query_filter', $configuration->getConfigTreeBuilder()->buildTree()->getName());
    }
}
