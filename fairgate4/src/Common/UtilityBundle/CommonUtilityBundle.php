<?php

namespace Common\UtilityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Common\UtilityBundle\DependencyInjection\Compiler\AddDbalCacheConfigurationPass;
use Common\UtilityBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class CommonUtilityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddDbalCacheConfigurationPass());
        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }
}
