<?php

namespace Pavlik\ElasticsearchBundle\Driver;

use Pavlik\ElasticsearchBundle\Annotation\Metadata;

interface MetadataCacheDriverInterface
{
    /**
     * @param string $class
     * @return Metadata|null
     */
    public function loadClassMetadata($class);

    /**
     * @param string $class
     * @param Metadata $metadata
     * @return bool
     */
    public function saveClassMetadata($class, Metadata $metadata);
}