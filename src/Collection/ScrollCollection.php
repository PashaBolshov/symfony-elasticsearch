<?php


namespace Pavlik\ElasticsearchBundle\Collection;

use Elastica\Scroll;
use Pavlik\ElasticsearchBundle\Manager;
use Pavlik\ElasticsearchBundle\Transformer\TransformerInterface;

class ScrollCollection implements CollectionInterface
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var int|null
     */
    protected $count = null;

    /**
     * @var Scroll
     */
    protected $scroll;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var int
     */
    protected $mode = self::MODE_ENTITY;

    /**
     * @param Scroll $scroll
     * @param Manager $manager
     */
    public function __construct(Scroll $scroll, Manager $manager) 
    {
        $this->scroll   = $scroll;
        $this->manager  = $manager;
        $this->position = 0;
    }

    protected function loadDocsFromScroll()
    {
        $resultSet = $this->scroll->current();
        $this->count = $resultSet->getTotalHits();
        $position = $this->position;
        foreach($resultSet->getDocuments() as $document) {
            $this->items[$position++] = $document;
        }
    }

    public function rewind() 
    {
        $this->scroll->rewind();
        $this->position = 0;
        $this->loadDocsFromScroll();
    }

    public function current() 
    {
        $item = $this->items[$this->position];
        if( $this->mode == self::MODE_DOCUNENT ) {
            return $item;
        }

        $metadata = $this->manager->loadDocumentMetadata($item);
        $transformer = $this->manager->getTransformer($metadata->getClassName());
        return $transformer->transformToEntity($item, $metadata);
    }

    public function key() 
    {
        return $this->position;
    }

    public function next() 
    {
        ++$this->position;

        if( ! $this->valid() ) {
            if( $this->count() > count($this->items) ) {
                $this->scroll->next();
                $this->loadDocsFromScroll();
            }
        }
    }

    public function valid() 
    {
        return isset($this->items[$this->position]);
    }

    public function count()
    {
        return $this->count;
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