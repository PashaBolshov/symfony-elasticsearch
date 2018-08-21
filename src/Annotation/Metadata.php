<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

class Metadata
{
    /**
     * @var string
     */
    protected $index;

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
     * @param array $options
     */
    public function addProperty($entityProperty, $elasticProperty, $options = [])
    {
        $this->elasticProperties[$elasticProperty] = $options;
        $this->entityProperties[$entityProperty] = $elasticProperty;
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
}