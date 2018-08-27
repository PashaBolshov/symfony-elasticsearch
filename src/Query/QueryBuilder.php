<?php

namespace Pavlik\ElasticsearchBundle\Query;

use Pavlik\ElasticsearchBundle\Manager;
use Pavlik\ElasticsearchBundle\Exception\ElasticsearchBundleException;
use Pavlik\ElasticsearchBundle\Annotation\EmbeddedMetadata;

use Elastica\QueryBuilder as BaseQueryBuilder;
use Elastica\QueryBuilder\Version;

class QueryBuilder extends BaseQueryBuilder
{
    /**
     * @Manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager, Version $version = null)
    {
        $this->manager = $manager;
        parent::__construct($version);
    }

    /**
     * @param string $alias
     * @param string $class
     * @return Querybuilder
     */
    public function addAlias($alias, $class)
    {
        $this->aliases[$alias] = $class;
        return $this;
    }

    /**
     * @param string $path
     * @return string
     */
    public function prop($path)
    {
        $path = explode('.', $path);
        $alias = array_shift($path);
        $class = $this->aliases[$alias] ?? null;

        if( empty($class) ) {
            throw new ElasticsearchBundleException('Undefined alias:' . $alias);
        }

        return $this->getElasticProperty($path, $this->manager->loadClassMetadata($class));
    }

    protected function getElasticProperty($path, $metadata)
    {
        $entityProperty = array_shift($path);

        $elasticProperty = $metadata->getEntityPropertyInfo($entityProperty);

        if( $elasticProperty instanceof EmbeddedMetadata ) { 
            $elasticProperty = $elasticProperty->getPrefix() . $this->getElasticProperty(
                $path, 
                $elasticProperty->getMetadata()
            );
        } else {
            if( ! empty($path) ) {
                $elasticProperty .= '.' . implode('.' , $path);
            }
        }

        return $elasticProperty;
    }
}