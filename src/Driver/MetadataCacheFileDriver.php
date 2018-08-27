<?php

namespace Pavlik\ElasticsearchBundle\Driver;

use Symfony\Component\Filesystem\Filesystem;
use Pavlik\ElasticsearchBundle\Annotation\Metadata;

class MetadataCacheFileDriver implements MetadataCacheDriverInterface
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param string $dir
     */
    public function __construct($dir)
    {
        $this->cacheDir = rtrim($dir, DIRECTORY_SEPARATOR);
        $this->filesystem = new Filesystem();

        if( ! $this->filesystem->exists($this->cacheDir) ) {
            $this->filesystem->mkdir($this->cacheDir);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadClassMetadata($class)
    {
        $fileName = md5($class);
        $path = $this->cacheDir . DIRECTORY_SEPARATOR . $fileName;

        if( ! $this->filesystem->exists($path) ) {
            return null;
        }

        $serialized = file_get_contents($path);
        $metadata = unserialize($serialized);

        if( $metadata instanceof Metadata ) {
            return $metadata;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function saveClassMetadata($class, Metadata $metadata)
    {
        $fileName = md5($class);
        $path = $this->cacheDir . DIRECTORY_SEPARATOR . $fileName;

        return file_put_contents($path, serialize($metadata));
    }
}