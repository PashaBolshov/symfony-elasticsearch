<?php


namespace Pavlik\ElasticsearchBundle\Collection;

use Elastica\ResultSet;
use Pavlik\ElasticsearchBundle\Manager;
use Pavlik\ElasticsearchBundle\Transformer\TransformerInterface;

class ResultSetCollection implements ExtendedCollectionInterface
{
    /**
     * @var ResultSet
     */
    protected $resultSet;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var int
     */
    protected $mode = self::MODE_ENTITY;

    /**
     * @param ResultSet $resultSet
     * @param Manager $manager
     */
    public function __construct(ResultSet $resultSet, Manager $manager) 
    {
        $this->resultSet = $resultSet;
        $this->manager   = $manager;
    }

    /**
     * {@inheritdoc} 
     */
    public function rewind() 
    {
        $this->resultSet->rewind();
    }

    /**
     * {@inheritdoc} 
     */
    public function getAggregations() 
    {
        return $this->resultSet->getAggregations();
    }

    /**
     * {@inheritdoc} 
     */
    public function getAggregation($name) 
    {
        return $this->resultSet->getAggregation($name);
    }

    /**
     * {@inheritdoc} 
     */
    public function hasAggregations() 
    {
        return $this->resultSet->hasAggregations();
    }

    /**
     * {@inheritdoc} 
     */
    public function hasSuggests()
    {
        return $this->resultSet->hasSuggests();
    }

    /**
     * {@inheritdoc} 
     */
    public function getSuggests()
    {
        return $this->resultSet->getSuggests();
    }

    /**
     * {@inheritdoc} 
     */
    public function current() 
    {
        $result = $this->resultSet->current();
        $item = $result->getDocument();

        if( $this->mode == self::MODE_DOCUMENT ) {
            return $item;
        }

        $metadata = $this->manager->loadDocumentMetadata($item);
        $transformer = $this->manager->getTransformer($metadata->getClassName());
        return $transformer->transformToEntity($item, $metadata);
    }

    /**
     * {@inheritdoc} 
     */
    public function key() 
    {
        return $this->resultSet->key();
    }

    /**
     * {@inheritdoc} 
     */
    public function next() 
    {
        $this->resultSet->next();
    }

    /**
     * {@inheritdoc} 
     */
    public function valid() 
    {
        return $this->resultSet->valid();
    }

    /**
     * {@inheritdoc} 
     */
    public function count()
    {
        return $this->resultSet->count();
    }

    /**
     * {@inheritdoc} 
     */
    public function getTotalHits()
    {
        return $this->resultSet->getTotalHits();
    }

    /**
     * {@inheritdoc} 
     */
    public function fetch($mode = self::MODE_ENTITY)
    {
        $this->mode = $mode;
        return $this;
    }
}