<?php


namespace Pavlik\ElasticsearchBundle\Collection;

use Elastica\ResultSet;
use Pavlik\ElasticsearchBundle\Manager;
use Pavlik\ElasticsearchBundle\Transformer\TransformerInterface;

class ResultSetCollection implements CollectionInterface
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

    public function rewind() 
    {
        $this->resultSet->rewind();
    }

    public function current() 
    {
        $result = $this->resultSet->current();
        $item = $result->getDocument();

        if( $this->mode == self::MODE_DOCUNENT ) {
            return $item;
        }

        $metadata = $this->manager->loadDocumentMetadata($item);
        $transformer = $this->manager->getTransformer($metadata->getClassName());
        return $transformer->transformToEntity($item, $metadata);
    }

    public function key() 
    {
        return $this->resultSet->key();
    }

    public function next() 
    {
        $this->resultSet->next();
    }

    public function valid() 
    {
        return $this->resultSet->valid();
    }

    public function count()
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