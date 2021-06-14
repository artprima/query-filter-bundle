<?php

declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\ParamConverter;

use Artprima\QueryFilterBundle\ParamConverter\ConfigConverter;
use Artprima\QueryFilterBundle\QueryFilter\Config\BaseConfig;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Class ConfigConverterTest.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class ConfigConverterTest extends TestCase
{
    public function testSupports()
    {
        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry
            ->expects($this->once())
            ->method('getManagers')
            ->willReturn([$manager]);

        $configuration = new ParamConverter([]);
        $configuration->setClass(BaseConfig::class);

        $converter = new ConfigConverter($registry);
        self::assertTrue($converter->supports($configuration));
    }

    public function testApply()
    {
        $repo = $this->getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'findAll', 'findBy', 'findOneBy', 'getClassName', 'find'])
            ->getMock();

        $manager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager
            ->expects($this->once())
            ->method('getRepository')
            ->with('DummyClass')
            ->willReturn($repo);

        $registry = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with('DummyClass')
            ->willReturn($manager);

        $configuration = new ParamConverter([]);
        $configuration->setName('dummy');
        $configuration->setClass(BaseConfig::class);
        $configuration->setOptions([
            'entity_manager' => null,
            'entity_class' => 'DummyClass',
            'repository_method' => 'getData',
        ]);

        $converter = new ConfigConverter($registry);

        $request = new HttpRequest([
            'limit' => 100,
            'page' => 3,
            'filter' => [
                'c.dummy' => 'the road to hell',
            ],
            'sortby' => 'c.id',
            'sortdir' => 'asc',
        ]);

        $result = $converter->apply($request, $configuration);

        $this->assertIsObject($request->attributes->get('dummy'));
        /** @var BaseConfig $v */
        $v = $request->attributes->get('dummy');
        $this->assertInstanceOf(BaseConfig::class, $v);
        $this->assertEquals(100, $v->getRequest()->getLimit());
        $this->assertEquals(3, $v->getRequest()->getPageNum());
        $this->assertEquals(['c.dummy' => 'the road to hell'], $v->getRequest()->getQuery());
        $this->assertEquals('c.id', $v->getRequest()->getSortBy());
        $this->assertEquals('asc', $v->getRequest()->getSortDir());
        $this->assertEquals('asc', $v->getRequest()->getSortDir());
        $this->assertIsCallable($v->getRepositoryCallback());

        $this->assertTrue($result);
    }
}
