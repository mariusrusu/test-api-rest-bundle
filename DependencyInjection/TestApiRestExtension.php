<?php
namespace EveryCheck\TestApiRestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class TestApiRestExtension extends ConfigurableExtension
{
    public function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(array(
            __DIR__ . '/../Resources/config/')));
        $loader->load('services.xml');
    }
}