<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

class NestedMetadata
{
    /**
     * @var Metatada
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $elasticProperty;

    /**
     * @param Metadata $metadata
     * @param string $elasticProperty
     */
    public function __construct($metadata, $elasticProperty)
    {
        $this->metadata = $metadata;
        $this->elasticProperty = $elasticProperty;
    }

    /**
     * Get the value of metadata
     *
     * @return  Metatada
     */ 
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get the value of elasticProperty
     *
     * @return  string
     */ 
    public function getElasticProperty()
    {
        return $this->elasticProperty;
    }
}