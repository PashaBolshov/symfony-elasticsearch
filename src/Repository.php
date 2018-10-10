<?php

namespace Pavlik\ElasticsearchBundle;

use Pavlik\ElasticsearchBundle\Annotation\Metadata;
use Pavlik\ElasticsearchBundle\Manager;
use Pavlik\ElasticsearchBundle\Collection\CollectionInterface;

class Repository
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @param Manager $manager
     * @param Metadata $metadata
     */
    public function __construct(Manager $manager, Metadata $metadata)
    {
        $this->manager = $manager;
        $this->metadata = $metadata;
    }

    /**
     * @param int $scrollSize Default 10.
     * @param string|int $contextTtl 1m == 60 == 60s
     * @return CollectionInterface
     * @throws
     */
    public function scrollAll($scrollSize = null, $contextTtl = '1m')
    {
        $persister = $this->manager->getTransshipment()->getPersister($this->metadata->getClassName());
        return $persister->scroll([], [], $scrollSize, $contextTtl);
    }

    /**
     * @param array $criteria
     * @param array $order
     * @param int $scrollSize Default 10.
     * @param string|int $contextTtl 1m == 60 == 60s
     * @return CollectionInterface
     */
    public function scrollBy($criterias = [], $order = [], $scrollSize = null, $contextTtl = '1m')
    {
        $persister = $this->manager->getTransshipment()->getPersister($this->metadata->getClassName());
        return $persister->scroll($criterias, $order, $scrollSize, $contextTtl);
    }

    /**
     * @param array $criteria
     * @param array $order
     * @param int $size
     * @param int $from
     * @return CollectionInterface
     */
    public function findBy($criterias = [], $order = [], $size = null, $from = null)
    {
        $persister = $this->manager->getTransshipment()->getPersister($this->metadata->getClassName());
        return $persister->load($criterias, $order, $size, $from);
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    protected function getQueryBuilder($alias)
    {
        $class = $this->metadata->getClassName();
        $qb = $this->manager->getQueryBuilder();
        $qb->addAlias($alias, $class);
        return $qb;
    }
}