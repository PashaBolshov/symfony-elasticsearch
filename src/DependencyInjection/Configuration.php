<?php

namespace Pavlik\ElasticsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pavlik_elasticsearch');

        $this->addManagersSection($rootNode);
        $this->addClientsSection($rootNode);
            
        return $treeBuilder;
    }

    /**
     * Adds the pavlik_elasticsearch.clients configuration
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addClientsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('clients')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('hosts')
                                ->defaultValue(array('127.0.0.1:9200'))
                                ->beforeNormalization()->ifString()->then(function($v) { return (array) $v; })->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Adds the pavlik_elasticsearch.managers configuration
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addManagersSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('managers')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('client')->defaultValue('default')->end()
                            ->scalarNode('alias')->defaultValue(null)->end()
                            ->scalarNode('indices_prefix')->defaultValue(null)->end()
                            ->arrayNode('indices') 
                                ->useAttributeAsKey('index')
                                ->arrayPrototype()                    
                                    ->children()
                                        ->arrayNode('settings')
                                            ->children()
                                                ->scalarNode('number_of_shards')->defaultValue(5)->end()
                                                ->scalarNode('number_of_replicas')->defaultValue(1)->end()
                                                ->arrayNode('index')
                                                    ->children()
                                                        ->scalarNode('refresh_interval')->defaultValue('30s')->end()
                                                    ->end()
                                                ->end()
                                                ->arrayNode('analysis')
                                                    ->children()
                                                        ->arrayNode('analyzer')
                                                            ->arrayPrototype()    
                                                                ->children()
                                                                    ->arrayNode('char_filter')
                                                                        ->scalarPrototype()->end()
                                                                    ->end()
                                                                    ->scalarNode('tokenizer')->defaultValue(null)->end()
                                                                    ->arrayNode('filter')
                                                                        ->scalarPrototype()->end()
                                                                    ->end()
                                                                ->end()
                                                            ->end()
                                                        ->end()
                                                        ->arrayNode('filter')
                                                            ->arrayPrototype()    
                                                                ->children()
                                                                    ->scalarNode('type')->defaultValue(null)->end()
                                                                    ->scalarNode('language')->defaultValue(null)->end()
                                                                    ->scalarNode('stopwords')->defaultValue(null)->end()
                                                                ->end()
                                                            ->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                                ->arrayNode('mapping')
                                                    ->children()         
                                                        ->scalarNode('type')->defaultValue('annotation')->end()                                           
                                                        ->scalarNode('class')->defaultValue(null)->end()
                                                    ->end()
                                                ->end()    
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    
}