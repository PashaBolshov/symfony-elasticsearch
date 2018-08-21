<?php

namespace Bpp\ElasticsearchBundle\Configuration;

class Configuration
{
    /**
     * @var array
     */
    protected $options;  
    
    /**
     * @param array $otpions
     */
    public function __construct(array $otpions)
    {
        $this->options = $otpions;
    }

    /**
     * @return string|null
     */
    public function getMetadataCacheDirectory()
    {
        return $this->options[Options::METADATA_CACHE_DIRECTORY] ?? null;
    }
}