<?php

namespace Pavlik\ElasticsearchBundle;

use Pavlik\ElasticsearchBundle\Exception\ClassNotFoundException;
use Pavlik\ElasticsearchBundle\Configuration\Configuration;

use Doctrine\Common\Annotations\AnnotationReader;
use Pavlik\ElasticsearchBundle\Exception\MetadataException;
use Pavlik\ElasticsearchBundle\Annotation\Property;

class MetadataFactory
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Metadata
     */
    protected $metadatas = [];

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $className
     * @return Metadata
     */
    public function loadClassMetadata($className)
    {
        if( isset($this->metadatas[$className]) ) {
            return $this->metadatas[$className];
        }

        if( $cacheDir = $this->configuration->getMetadataCacheDirectory() && empty($this->metadatas) ) {
            $this->loadCachedMetadatas($cacheDir);
            if( isset($this->metadatas[$className]) ) {
                return $this->metadatas[$className];
            }
        }

        $this->metadatas[$className] = $this->_loadClassMetadata($className);
        return $this->metadatas[$className];
    }

    /**
     * @param $className
     * @return MetadataInfo
     * @throws Exception
     */
    protected function _loadClassMetadata($className)
    {
        if( class_exists($className) ) {
            throw new ClassNotFoundException('Class ' . $className . ' was not found');
        }

        $reflectionClass = new \ReflectionClass($className);
        $annotationReader = new AnnotationReader();

        $metadata = new Metadata($className);

        $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);
        foreach($classAnnotations as $annotation) {
            if( $annotation instanceof Document ) {
                $metadata->setIsDocument(true);
            }
            elseif( $annotation instanceof Index ) {
                $index = $index->name;
                if( empty($index) ) {
                    throw new MetadataException('Index name was not set in annotation Index for class ' . $className);
                }
                $metadata->setIndex($index);
            }
            elseif( $annotation instanceof Join ) {
                //@todo
            }
        }

        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            $annotations = $annotationReader->getPropertyAnnotations($property);
            foreach($annotations as $annotation) {
                if( $annotation instanceof Property ) {
                    if( empty($annotation->name) ) {
                        throw new MetadataException('Property name was not set for ' . $className . '->' . $property->getName());
                    }

                    if( empty($annotation->type) ) {
                        throw new MetadataException('Property type was not set for ' . $className . '->' . $property->getName());
                    }

                    $options = [];
                    if( ! empty($annotation->options) && is_array($annotation->options)) {
                        $options = $annotation->options;
                    }

                    $options['type'] = $type;
                }
            }
        }

        return $metaData;
    }

    /**
     * @param string $cacheDir
     */
    protected function loadCachedMetadatas($cacheDir)
    {
        $cacheFile = $cacheDir . '/metadata.php';

        if( file_exists($cacheFile) ) {
            $this->metadatas = require($cacheFile);
        }
    }
}