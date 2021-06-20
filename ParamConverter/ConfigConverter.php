<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\ParamConverter;

use Artprima\QueryFilterBundle\Exception\InvalidArgumentException;
use Artprima\QueryFilterBundle\QueryFilter\Config\ConfigInterface;
use Artprima\QueryFilterBundle\QueryFilter\Request;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class ConfigConverter implements ParamConverterInterface
{
    public function __construct(private ?ManagerRegistry $registry)
    {
    }

    private function getOptions(ParamConverter $configuration): array
    {
        return array_replace([
            'entity_manager' => null,
            'entity_class' => null,
            'repository_method' => null,
        ], $configuration->getOptions());
    }

    private function getManager($name, $class): EntityManager
    {
        if (null === $name) {
            $result = $this->registry->getManagerForClass($class);
        } else {
            $result = $this->registry->getManager($name);
        }

        if (!$result instanceof EntityManager) {
            throw new InvalidArgumentException(self::class.': expected EntityManager instance. Wrong configuration?');
        }

        return $result;
    }

    /**
     * Stores the object in the request.
     *
     * Usage example:
     *
     * @ParamConverter("config", class="AppBundle\QueryFilter\Config\BrandFilterConfig",
     *                           converter="query_filter_config_converter",
     *                           options={"entity_class": "AppBundle:Brand", "repository_method": "findByOrderBy"})
     *
     * @param HttpRequest    $request       The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     *
     * @throws \RuntimeException
     */
    public function apply(HttpRequest $request, ParamConverter $configuration): bool
    {
        $options = $this->getOptions($configuration);

        if (!isset($options['entity_class'])) {
            throw new InvalidArgumentException(self::class.': entity_class not provided. Wrong configuration?');
        }

        if (!isset($options['repository_method'])) {
            throw new InvalidArgumentException(self::class.': repository_method not provided. Wrong configuration?');
        }

        $configClassName = $configuration->getClass();
        $config = new $configClassName();

        if (!$config instanceof ConfigInterface) {
            throw new InvalidArgumentException(self::class.': config is not QueryFilterConfig descendant. Wrong configuration?');
        }

        $config->setRequest(new Request($request));

        $manager = $this->getManager($options['entity_manager'], $options['entity_class']);
        $repo = $manager->getRepository($options['entity_class']);

        if (!is_callable([$repo, $options['repository_method']])) {
            throw new InvalidArgumentException(self::class.': repository_method is not callable. Wrong configuration?');
        }

        $config->setRepositoryCallback([$repo, $options['repository_method']]);

        $request->attributes->set($configuration->getName(), $config);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return bool true if the object is supported, else false
     */
    public function supports(ParamConverter $configuration): bool
    {
        // if there is no manager, this means that only Doctrine DBAL is configured
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }

        if (!$configuration->getClass()) {
            return false;
        }

        if (!class_exists($configuration->getClass())) {
            return false;
        }

        return in_array(ConfigInterface::class, class_implements($configuration->getClass()), true);
    }
}
