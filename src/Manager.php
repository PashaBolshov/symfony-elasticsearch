<?php

namespace Pavlik\ElasticsearchBundle;

use Pavlik\ElasticsearchBundle\Client\Client;
use Pavlik\ElasticsearchBundle\Annotation\MetadataFactory;
use Pavlik\ElasticsearchBundle\Configuration\Configuration;
use Pavlik\ElasticsearchBundle\Annotation\Metadata;
use Pavlik\ElasticsearchBundle\Transformer\Transformer;
use Pavlik\ElasticsearchBundle\Transformer\TransformerInterface;

use Elastica\Document;
use Pavlik\ElasticsearchBundle\Query\QueryBuilder;


class Manager
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Configuration
     */
    protected $conf;

    /**
     * @var MetadataFactory;
     */
    protected $metadataFactory;

    /**
     * @var Transshipment
     */
    protected $transshipment;

    /**
     * @var Repostiory[]
     */
    protected $repositories = [];

    /**
     * @var TransformerInterface[]
     */
    protected $transformers = [];


    /**
     * @param Client $client
     */
    public function __construct(Client $client, Configuration $conf)
    {
        $this->client = $client;
        $this->conf   = $conf;

        $this->transshipment = new Transshipment($this);
        $this->metadataFactory = new MetadataFactory($this);
    }

    /**
     * @param Document $document
     * @return Metadata
     */
    public function loadDocumentMetadata(Document $document)
    {
        return $this->metadataFactory->getMetadataByIndexNameAndType(
            $document->getParam('_index'),
            $document->getParam('_type')
        );
    }

    /**
     * @param string $className
     * @return TransformerInterface
     */
    public function getTransformer($className)
    {   
        if( isset($this->transformers[$className]) ) {
            return $this->transformers[$className];
        }

        $this->transformers[$className] = new Transformer();
        return $this->transformers[$className];
    }

    /**
     * @return Repostiory
     * @throws
     */
    public function getRepository($className)
    {
        if( isset($this->repositories[$className]) ) {
            return $this->repositories[$className];
        }

        $metadata = $this->metadataFactory->loadClassMetadata($className);
        $class = $metadata->getRepositoryClass();
        $this->repositories[$className] = new $class($this, $metadata);
        return $this->repositories[$className];
    }

    /**
     * @param string $className
     * @return Metadata
     */
    public function loadClassMetadata($className)
    {
        return $this->metadataFactory->loadClassMetadata($className);
    }

    /**
     * @param object $entity
     */
    public function index($entity)
    {
        $this->transshipment->index($entity);
    }

    /**
     * @param object $entity
     */
    public function update($entity)
    {
        $this->transshipment->update($entity);
    }

    /**
     * @param object $entity
     */
    public function delete($entity)
    {
        $this->transshipment->delete($entity);
    }

    /**
     * @throws
     */
    public function flush()
    {
        $this->transshipment->flush();
    }


    /**
     * Get the value of transshipment
     *
     * @return  Transshipment
     */ 
    public function getTransshipment()
    {
        return $this->transshipment;
    }

    /**
     * Get the value of conf
     *
     * @return  Configuration
     */ 
    public function getConfiguration()
    {
        return $this->conf;
    }

    /**
     * Get the value of client
     *
     * @return  Client
     */ 
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return new QueryBuilder($this);
    }
}