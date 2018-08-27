<?php

namespace Pavlik\ElasticsearchBundle\Driver;

use Pavlik\ElasticsearchBundle\Annotation\Metadata;

class MetadataCacheArrayDriver implements MetadataCacheDriverInterface
{
    protected $metas = [];

    /**
     * @param string $class
     * @return Metadata|null
     */
    public function loadClassMetadata($class)
    {
        return $this->metas[$class] ?? null;
    }

    /**
     * @param string $class
     * @param Metadata $metadata
     * @return bool
     */
    public function saveClassMetadata($class, Metadata $metadata)
    {
        $this->metas[$class] = $metadata;
        return true;
    }
}