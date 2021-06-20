<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\tests\functional\Test;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakePublicCompilerPass implements CompilerPassInterface
{
    private $class;

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition($this->class)) {
            throw new LogicException($this->class.' must be registered');
        }

        $conditionManagerDefinition = $container->getDefinition($this->class);
        $conditionManagerDefinition->setPublic(true);
    }
}
