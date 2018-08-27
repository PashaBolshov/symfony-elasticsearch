<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

class Metadata
{
    /**
     * @var string
     */
    protected $index;

    /**
     * @var string|null
     */
    protected $indexPrefix;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $repositoryClass;

    /**
     * @var bool
     */
    protected $isDocument = false;

    /**
     * List of document properties in elasticSearch
     * @var array
     */
    protected $elasticProperties = [];

    /**
     * Elastic entities witch are parameters
     * @var array
     */
    protected $elasticParameters = [];

    /**
     * List of entity properties
     * @var array
     */
    protected $entityProperties = [];

    /**
     * @var string
     */
    protected $className;
    
    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * Get the value of index
     *
     * @return  string
     */ 
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Get the name of index
     *
     * @return  string
     */ 
    public function getIndexName()
    {
        return $this->getIndexPrefix() . $this->getIndex();
    }

    /**
     * Set the value of index
     *
     * @param  string  $index
     *
     * @return  self
     */ 
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Get the value of className
     *
     * @return  string
     */ 
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $entityProperty
     * @param string $elasticProperty
     */
    public function addEntityProperty($entityProperty, $elasticProperty)
    {
        $this->entityProperties[$entityProperty] = $elasticProperty;
    }

    /**
     * @param string $entityProperty
     * @return mixed
     */
    public function getEntityPropertyInfo($entityProperty)
    {
        return $this->entityProperties[$entityProperty] ?? null;
    }

    /**
     * @return string|null
     */
    public function getEntityIdProperty()
    {
        return $this->elasticParameters['_id'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getEntityVersionProperty()
    {
        return $this->elasticParameters['_version'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getEntityRoutingProperty()
    {
        return $this->elasticParameters['_routing'] ?? null;
    }

    /**
     * @param string $elasticProperty
     * @param array $options
     * @param bool $isParameter
     */
    public function addElasticProperty($elasticProperty, $options = [], $isParameter = false)
    {
        $this->elasticProperties[$elasticProperty] = $options;
        if( $isParameter ) {

            $property = null;
            foreach($this->getEntityProperties() as $entityProprty => $_elasticProperty) {
                if( $elasticProperty == $_elasticProperty ) {
                    $property = $entityProprty;
                }
            }
    
            $this->elasticParameters[$elasticProperty] = $property;
        }
    }

    /**
     * @param mixed $elasticProperty
     * @return bool
     */
    public function isElasticParameter($elasticProperty)
    {
        if( ! is_scalar($elasticProperty) ) return false;
        return isset($this->elasticParameters[$elasticProperty]);
    }

    /**
     * @return string|null
     */
    public function getElasticJoinPropertyName()
    {
        foreach($this->getElasticProperties() as $name => $options) {
            if( 'join' == $options['type'] ?? null ) return $name;
        }

        return null;
    }

    /**
     * Get the value of isDocument
     *
     * @return  bool
     */ 
    public function isDocument()
    {
        return $this->isDocument;
    }

    /**
     * Set the value of isDocument
     *
     * @param  bool  $isDocument
     *
     * @return  self
     */ 
    public function setIsDocument(bool $isDocument)
    {
        $this->isDocument = $isDocument;

        return $this;
    }

    /**
     * Get the value of type
     *
     * @return  string
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @param  string  $type
     *
     * @return  self
     */ 
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of repositoryClass
     *
     * @return  string
     */ 
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * Set the value of repositoryClass
     *
     * @param  string  $repositoryClass
     *
     * @return  self
     */ 
    public function setRepositoryClass($repositoryClass)
    {
        $this->repositoryClass = $repositoryClass;

        return $this;
    }

    /**
     * Get list of document properties in elasticSearch
     *
     * @return  array
     */ 
    public function getElasticProperties()
    {
        return $this->elasticProperties;
    }

    /**
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws
     */
    public function getEntityPropertyValue($object, $property)
    {
        return call_user_func([$object, 'get' . ucwords($property)]);
    }

    /**
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws
     */
    public function setEntityPropertyValue($object, $property, $value)
    {
        return call_user_func([$object, 'set' . ucwords($property)], $value);
    }

    /**
     * Get the value of indexPrefix
     *
     * @return  string|null
     */ 
    public function getIndexPrefix()
    {
        return $this->indexPrefix;
    }

    /**
     * Set the value of indexPrefix
     *
     * @param  string|null  $indexPrefix
     *
     * @return  self
     */ 
    public function setIndexPrefix($indexPrefix)
    {
        $this->indexPrefix = $indexPrefix;

        return $this;
    }

    /**
     * Get list of entity properties
     *
     * @return  array
     */ 
    public function getEntityProperties()
    {
        return $this->entityProperties;
    }
}