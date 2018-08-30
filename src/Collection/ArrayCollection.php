<?php

namespace Pavlik\ElasticsearchBundle\Collection;

use Pavlik\ElasticsearchBundle\Manager;
use Elastica\Document;


class ArrayCollection implements CollectionInterface
{
    /**
     * @var int
     */
    protected $mode = self::MODE_ENTITY;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Document[]
     */
    protected $items = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @param Document[] $items
     * @param Manager $manager
     */
    public function __construct($items, Manager $manager) 
    {
        $this->items = $items;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc} 
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc} 
     */
    public function fetch($mode = self::MODE_ENTITY)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * {@inheritdoc} 
     */
    public function current() 
    {
        $item = $this->items[$this->position];

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
        return $this->position;
    }

    /**
     * {@inheritdoc} 
     */
    public function next() 
    {
        $this->position++;
    }

    /**
     * {@inheritdoc} 
     */
    public function valid() 
    {
        return isset($this->items[$this->position]);
    }

    /**
     * {@inheritdoc} 
     */
    public function count()
    {
        return count($this->items);
    }
}