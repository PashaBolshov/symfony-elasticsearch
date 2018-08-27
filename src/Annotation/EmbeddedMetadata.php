<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

class EmbeddedMetadata
{
    /**
     * @var Metatada
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $prefix;


    public function __construct($metadata, $prefix)
    {
        $this->metadata = $metadata;
        $this->prefix = $prefix;
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
     * Get the value of prefix
     *
     * @return  string
     */ 
    public function getPrefix()
    {
        return $this->prefix;
    }
}