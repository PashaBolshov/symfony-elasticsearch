<?php

namespace Pavlik\ElasticsearchBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Pavlik\ElasticsearchBundle\Client\Client;
use Pavlik\ElasticsearchBundle\Manager;
use Symfony\Component\DependencyInjection\Alias;
use Pavlik\ElasticsearchBundle\Driver\MetadataCacheFileDriver;
use Pavlik\ElasticsearchBundle\DependencyInjection\Configuration;
use Pavlik\ElasticsearchBundle\Configuration\Configuration as ManagerConfiguration;
use Pavlik\ElasticsearchBundle\Driver\MetadataCacheArrayDriver;
use Pavlik\ElasticsearchBundle\Configuration\Options;
use Pavlik\ElasticsearchBundle\ContainerRegistry;


class PavlikElasticsearchExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $clientsDefenitionsIds = [];
        $clientsConfigurations = $config['clients'] ?? [];
        foreach($clientsConfigurations as $name => $configuration) {
            $configuration['name'] = $name;
            $definitionId = $this->defineClient($configuration, $container);
            $clientsDefenitionsIds[$name] = $definitionId;
        }


        $managersDefenitionsIds = [];
        $managersConfigurations = $config['managers'] ?? [];
        foreach($managersConfigurations as $name => $configuration) {
            $configuration['name'] = $name;
            $definitionId = $this->defineManager($configuration, $container);
            $managersDefenitionsIds[$name] = $definitionId;
            if( isset($configuration['alias']) ) {
                $managersDefenitionsIds[$configuration['alias']] = $definitionId;
            }
        }

        $this->defineElasticsearch($container, $clientsDefenitionsIds, $managersDefenitionsIds);
    }

    /**
     * @param array $configuration
     * @param ContainerBuilder $container
     */
    protected function defineClient(array $configuration, ContainerBuilder $container)
    {
        $name = $configuration['name'];
        $parameterId = sprintf('pavlik_elasticsearch.client.%s', $name);
        $defenationClass = Client::class;

        if( ! empty($configuration['hosts']) ) {
            $servers = [];
            foreach($configuration['hosts'] as $host) {
                $params = explode(':', $host);
                $servers[] = [   
                    'host' => $params[0],
                    'port' => $params[1] ?? 9200
                ];
            }

            $clientParams = [
                'servers'    => $servers,
                'roundRobin' => true,
                'timeout'    => 60
            ];

            $parameterDef = new Definition($defenationClass);
            $parameterDef->setPublic(false);
            $parameterDef->addArgument($clientParams);
            $container->setDefinition($parameterId, $parameterDef);

            if( $name === 'default' ) {
                $container->setAlias('pavlik_elasticsearch.client', new Alias($parameterId, true));
                $container->setAlias($defenationClass, $parameterId);
            }
        }

        return $parameterId;
    }

    /**
     * @param array $managers
     * @param ContainerBuilder $container
     */
    protected function defineManager(array $configuration, ContainerBuilder $container)
    {
        $name = $configuration['name'];
        $parameterId = sprintf('pavlik_elasticsearch.manager.%s', $name);
        $defenationClass = Manager::class;
        
        $parameterDef = new Definition($defenationClass);
        $parameterDef->setPublic(true);

        $clientId = sprintf('pavlik_elasticsearch.client.%s', $configuration['client'] ?? 'default');
        $parameterDef->addArgument(new Reference($clientId));

        $configDefinitionId = $this->defineManagerConfiguration($name, $configuration, $container);
        $parameterDef->addArgument(new Reference($configDefinitionId));
    
        $container->setDefinition($parameterId, $parameterDef);

        $alias = $configuration['alias'] ?? null;
        if( ! is_null($alias ) ) {
            $container->setAlias(sprintf('pavlik_elasticsearch.manager.%s', $alias), new Alias($parameterId, true));
        }

        if( $name == 'default' ) {
            $container->setAlias('pavlik_elasticsearch.manager', new Alias($parameterId, true));
            $container->setAlias($defenationClass, new Alias($parameterId, false));
        }
        
        return $parameterId;
    }

    /**
     * @param string $managerName
     * @param array $configuration
     * @param ContainerBuilder $container
     * @return string 
     */
    protected function defineManagerConfiguration($managerName, $configuration, ContainerBuilder $container)
    {
        $definitionId = sprintf('pavlik_elasticsearch.manager.%s.configuration', $managerName);
        $definition = new Definition(ManagerConfiguration::class);
        
        $cacheDefinitionId = $this->defineMetaCacheDriver($managerName, $container);
        $definition->addMethodCall('setMetaCacheDriver', array(new Reference($cacheDefinitionId)));

        if( $indicesPrefix = $configuration[Options::INDICES_PREFIX] ?? null ) {
            $definition->addMethodCall('setIndicesPrefix', array($indicesPrefix));
        }

        $container->setDefinition($definitionId, $definition);
        return $definitionId;
    }

    /**
     * @param string $managerName
     * @param ContainerBuilder $container
     * @return string
     */
    protected function defineMetaCacheDriver($managerName, ContainerBuilder $container)
    {
        $environment = $container->getParameter('kernel.environment');
        if( $environment !== 'dev' ) {
            $parameterDef = new Definition(MetadataCacheFileDriver::class);
            $parameterDef->addArgument("%kernel.cache_dir%/pavlik_elasticsearch");
        } else {
            $parameterDef = new Definition(MetadataCacheArrayDriver::class);
        }

        $cacheDefinitionId = sprintf('pavlik_elasticsearch.manager.%s.meta_cache_driver', $managerName);
        $container->setDefinition($cacheDefinitionId, $parameterDef);

        return $cacheDefinitionId;
    }

    /**
     * @param array $clients
     * @param array $managers
     * @param ContainerBuilder $container
     * @return string
     */
    protected function defineElasticsearch(ContainerBuilder $container, $clients, $managers)
    {
        $parameterDef = new Definition(ContainerRegistry::class);
        $parameterDef->setPublic(false);
        $parameterDef->addArgument(new Reference('service_container'));
        $parameterDef->addArgument($clients);
        $parameterDef->addArgument($managers);
        $parameterDef->addArgument('default');
        $parameterDef->addArgument('default');
        $container->setDefinition('pavlik_elasticsearch', $parameterDef);

        $container->setAlias(ContainerRegistry::class, new Alias('pavlik_elasticsearch', true));
    }
}