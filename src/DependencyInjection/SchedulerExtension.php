<?php

namespace Mikamatto\Scheduler\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class SchedulerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // Process configuration if any
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('scheduler.config', $config);

        // Load service definitions
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        // Load routing file (automatically load routes for the bundle)

    }

}