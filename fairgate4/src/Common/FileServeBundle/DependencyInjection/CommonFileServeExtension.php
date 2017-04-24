<?php

namespace Common\FileServeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class CommonFileServeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setAlias('common_file_serve.response_factory', sprintf('common_file_serve.response_factory.%s', $config['factory']));
        $container->setParameter('common_file_serve.base_dir', $config['base_dir']);
        $container->setParameter('common_file_serve.skip_file_exists', $config['skip_file_exists']);
    }
}
