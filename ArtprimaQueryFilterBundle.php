<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle;

use Artprima\QueryFilterBundle\DependencyInjection\Compiler\AddQueryBuilderConditionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ArtprimaQueryFilterBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddQueryBuilderConditionPass());
    }
}
