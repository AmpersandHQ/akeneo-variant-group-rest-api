<?php
namespace Ampersand\Bundle\VariantGroupRestApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AmpersandVariantGroupRestApiExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('steps.yml');
        $loader->load('processors.yml');
        $loader->load('writers.yml');
    }
}
