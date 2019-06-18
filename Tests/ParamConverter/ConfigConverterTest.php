<?php declare(strict_types=1);

namespace Tests\Artprima\QueryFilterBundle\ParamConverter;

use Artprima\QueryFilterBundle\ParamConverter\ConfigConverter;
use Artprima\QueryFilterBundle\QueryFilter\Config\BaseConfig;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Class ConfigConverterTest
 *
 * @author Denis Voytyuk <denis@voituk.ru>
 *
 * @package Tests\Artprima\QueryFilterBundle\ParamConverter
 */
class ConfigConverterTest extends TestCase
{
    public function testSupports()
    {
        $manager = self::getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry = self::getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry
            ->expects(self::once())
            ->method('getManagers')
            ->willReturn([$manager]);

        $configuration = new ParamConverter([]);
        $configuration->setClass(BaseConfig::class);

        $converter = new ConfigConverter($registry);
        self::assertTrue($converter->supports($configuration));
    }

    public function testApply()
    {
        $repo = self::getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'findAll', 'findBy', 'findOneBy', 'getClassName', 'find'])
            ->getMock();

        $manager = self::getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager
            ->expects(self::once())
            ->method('getRepository')
            ->with('DummyClass')
            ->willReturn($repo);

        $registry = self::getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry
            ->expects(self::once())
            ->method('getManagerForClass')
            ->with('DummyClass')
            ->willReturn($manager);

        $configuration = new ParamConverter([]);
        $configuration->setClass(BaseConfig::class);
        $configuration->setOptions([
            'entity_manager' => null,
            'entity_class' => 'DummyClass',
            'repository_method' => 'getData',
        ]);

        $converter = new ConfigConverter($registry);
        self::assertTrue($converter->apply(new HttpRequest([
            'limit' => 100,
            'page'=> 3,
            'filter' => [
                'c.dummy' => 'the road to hell',
            ],
            'sortby' => 'c.id',
            'sortdir' => 'asc',
        ]), $configuration));
    }
}
