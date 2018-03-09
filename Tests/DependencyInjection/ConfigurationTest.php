<?php declare(strict_types = 1);

namespace Tests\Artprima\QueryFilterBundle\DependencyInjection;

use Artprima\QueryFilterBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigurationTest
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Tests\Artprima\QueryFilterBundle\DependencyInjection
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