<?php
namespace EveryCheck\TestApiRestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class TestApiRestExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(array(
            __DIR__ . '/../Resources/config/')));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $config);

        $container->setParameter('test_api_rest.directory.payloads', $config['directory']['payloads']);
        $container->setParameter('test_api_rest.directory.responses', $config['directory']['responses']);

    }
}