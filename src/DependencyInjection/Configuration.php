<?php

namespace Mikamatto\Scheduler\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('scheduler');

        $treeBuilder->getRootNode()
            ->children()
                // Define your configuration options here
                // Example:
                // ->scalarNode('option_name')->defaultValue('default')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}