<?php

namespace Pavlik\ElasticsearchBundle\Persister;

use Pavlik\ElasticsearchBundle\Annotation\Metadata;
use Pavlik\ElasticsearchBundle\Client\Client;
use Pavlik\ElasticsearchBundle\Manager;
use Pavlik\ElasticsearchBundle\Collection\ScrollCollection;
use Pavlik\ElasticsearchBundle\Collection\CollectionInterface;
use Pavlik\ElasticsearchBundle\Collection\ResultSetCollection;


use Elastica\Search;
use Elastica\Scroll;
use Elastica\Bulk\Action\AbstractDocument;


class Persister
{
    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var Manager 
     */
    protected $manager;

    /**
     * @param Metadata $metadata
     * @param Manager $manager
     */
    public function __construct(Metadata $metadata, Manager $manager)
    {
        $this->metadata = $metadata;
        $this->manager = $manager;
    }

    /**
     * @return CollectionInterface
     */
    public function loadAll()
    {
        $search = new Search($this->manager->getClient());
        $search->addIndex($this->metadata->getIndexName())
            ->addType($this->metadata->getType())
            ->getQuery()
            ->setSize(10000);
        
        $scroll = new Scroll($search);

        return new ScrollCollection($scroll, $this->manager);
    }

    /**
     * @return CollectionInterface
     */
    public function load($criterias = [])
    {
        $qb = $this->manager->getQueryBuilder();
        $qb->addAlias('e', $this->metadata->getClassName());

        $query = $qb->query()->bool();

        foreach($criterias as $entityProperty => $value ) {
            if( is_array($value) ) {
                $filter = $qb->query()->terms($qb->prop('e.' . $entityProperty), $value);
            }
            else {
                $filter = $qb->query()->term([$qb->prop('e.' . $entityProperty) => $value]);
            }

            $query->addMust($filter);
        }

        $search = new Search($this->manager->getClient());
        $search->addIndex($this->metadata->getIndexName())
            ->addType($this->metadata->getType())
            ->setQuery($query)
            ->getQuery()
            ->setSize(10000);

        $resultSet = $search->search();

        return new ResultSetCollection($resultSet, $this->manager);
    }    

    public function getDeleteAction($entity)
    {
        return $this->getAction($entity, AbstractDocument::OP_TYPE_DELETE);
    }

    public function getUpdateAction($entity)
    {
        return $this->getAction($entity, AbstractDocument::OP_TYPE_UPDATE);
    }

    public function getIndexAction($entity)
    {
        return $this->getAction($entity, AbstractDocument::OP_TYPE_INDEX);
    }

    protected function getAction($entity, $actionType)
    {
        $transformer = $this->manager->getTransformer($this->metadata->getClassName());
        $document = $transformer->transformToDoc($entity, $this->metadata);

        return AbstractDocument::create($document, $actionType);
    }
}