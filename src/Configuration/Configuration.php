<?php

namespace Pavlik\ElasticsearchBundle\Configuration;

use Pavlik\ElasticsearchBundle\Driver\MetadataCacheDriverInterface;
use Pavlik\ElasticsearchBundle\Driver\MetadataCacheArrayDriver;

class Configuration
{
    /**
     * @var array
     */
    protected $options;  
    
    /**
     * @param array $otpions
     */
    public function __construct(array $otpions = [])
    {
        $this->options = $otpions;
    }

    /**
     * @param string $prefix
     * @return Configuration
     */
    public function setIndicesPrefix($prefix)
    {
        $this->options[Options::INDICES_PREFIX] = $prefix;
        return $this;
    }

    /**
     * @param string $prefix
     */
    public function getIndicesPrefix()
    {
        return $this->options[Options::INDICES_PREFIX] ?? null;
    }

    /**
     * @param MetadataCacheDriverInterface $driver
     * @return Configuration
     */
    public function setMetaCacheDriver(MetadataCacheDriverInterface $driver)
    {
        $this->options[Options::METADATA_CACHE_DRIVER] = $driver;
        return $this; 
    }

    /**
     * @return MetadataCacheDriverInterface
     */
    public function getMetaCacheDriver()
    {
        if( ! isset($this->options[Options::METADATA_CACHE_DRIVER]) ) {
            $this->options[Options::METADATA_CACHE_DRIVER] = new MetadataCacheArrayDriver();
        }

        return $this->options[Options::METADATA_CACHE_DRIVER];
    }
}