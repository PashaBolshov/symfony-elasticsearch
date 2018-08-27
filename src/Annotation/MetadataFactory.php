<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

use Pavlik\ElasticsearchBundle\Exception\ClassNotFoundException;

use Doctrine\Common\Annotations\AnnotationReader;
use Pavlik\ElasticsearchBundle\Exception\MetadataException;
use Pavlik\ElasticsearchBundle\Repository;
use Pavlik\ElasticsearchBundle\Driver\MetadataCacheDriverInterface;
use Pavlik\ElasticsearchBundle\Configuration\Configuration;
use Pavlik\ElasticsearchBundle\Manager;

class MetadataFactory
{
    /**
     * @var Metadata
     */
    protected $metadatas = [];

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Manager $manager)
    {
        $this->configuration = $manager->getConfiguration();
    }

    /**
     * @param string $indexName
     * @param string $type
     * @return Metadata|null
     */
    public function getMetadataByIndexNameAndType($indexName, $type)
    {
        static $cache = [];
        $key = $indexName . '##' . $type;

        if( isset($cache[$key])) {
            return $cache[$key];
        }

        foreach($this->metadatas as $metadata) {
            if($metadata->getIndexName() == $indexName && $metadata->getType() == $type) {
                $cache[$key] = $metadata;
                break;
            }
        }

        return $cache[$key] ?? null;
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

        $this->loadCachedMetadata($className);
        if( isset($this->metadatas[$className]) ) {
            return $this->metadatas[$className];
        }

        $this->metadatas[$className] = $this->parseClassMetadata($className);
        $this->cacheMetadata($className, $this->metadatas[$className]);
        return $this->metadatas[$className];
    }

    /**
     * @param $className
     * @return MetadataInfo
     * @throws Exception
     */
    protected function parseClassMetadata($className)
    {
        if( ! class_exists($className) ) {
            throw new ClassNotFoundException('Class ' . $className . ' was not found');
        }

        $reflectionClass = new \ReflectionClass($className);
        $annotationReader = new AnnotationReader();

        $metadata = new Metadata($className);

        $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);
        foreach($classAnnotations as $annotation) {
            if( $annotation instanceof Document ) {
                $metadata->setIsDocument(true);
                $repository = $annotation->repository;
                if( empty($repository) ) {
                    $repository = Repository::class;
                }

                if( ! class_exists($repository) ) {
                    throw new MetadataException('Repository class ' . $repository . ' does not exist. (Used in class ' . $className . ')');
                }

                $metadata->setRepositoryClass($repository);

            }
            elseif( $annotation instanceof Index ) {
                $index = $annotation->name;
                if( empty($index) ) {
                    throw new MetadataException('Index name was not set in annotation Index for class ' . $className);
                }

                $metadata->setIndex($index);
                $metadata->setIndexPrefix($this->configuration->getIndicesPrefix());

                $type = $annotation->type;
                if( empty($type) ) {
                    throw new MetadataException('Type was not set in annotation Index for class ' . $className);
                }
                $metadata->setType($type);
            }
            elseif( $annotation instanceof Join ) {
                $options = [
                    'relations' => $annotation->relations,
                    'type'      => 'join'
                ];
                $metadata->addElasticProperty($annotation->property, $options);
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

                    $options['type'] = $annotation->type;
                    $metadata->addEntityProperty($property->getName(), $annotation->name);
                    $metadata->addElasticProperty($annotation->name, $options);

                } elseif ( $annotation instanceof Embedded ) {
                    $class = $annotation->class;
                    if( ! class_exists($class) ) {
                        throw new MetadataException('Class ' . $class . ' does not exist');
                    }

                    $classMetadata = $this->parseClassMetadata($class);
                    $prefix = $annotation->prefix;

                    $embeddedMetadata = new EmbeddedMetadata($classMetadata, $prefix);
                    $metadata->addEntityProperty($property->getName(), $embeddedMetadata);

                    $elasticProperties = $classMetadata->getElasticProperties();
                    foreach($elasticProperties as $elasticProperty => $options) {
                        $elasticProperty = $prefix . $elasticProperty;
                        $metadata->addElasticProperty($elasticProperty, $options);
                    }

                } elseif ( $annotation instanceof Parameter ) {
                    $metadata->addEntityProperty($property->getName(), $annotation->name);
                    $metadata->addElasticProperty($annotation->name, [], true);

                } elseif ( $annotation instanceof JoinName ) {
                    $metadata->addEntityProperty($property->getName(), $annotation);

                } elseif ( $annotation instanceof JoinParent ) {
                    $metadata->addEntityProperty($property->getName(), $annotation);
                }
            }
        }

        return $metadata;
    }

    /**
     * @param string $class
     */
    protected function loadCachedMetadata($class)
    {
        $metadata = $this->configuration->getMetaCacheDriver()->loadClassMetadata($class);
        if( $metadata ) {
            $this->metadatas[$class] = $metadata;
        }
    }

    /**
     * @param string $class
     */
    protected function cacheMetadata($class, Metadata $metadata)
    {
        if( ! $this->configuration->getMetaCacheDriver()->saveClassMetadata($class, $metadata) ) {
            throw new MetadataException('Could not save metadata for ' . $class);
        }
    }
}