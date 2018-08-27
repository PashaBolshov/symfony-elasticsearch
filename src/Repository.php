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
     * @return CollectionInterface
     * @throws
     */
    public function findAll()
    {
        $persister = $this->manager->getTransshipment()->getPersister($this->metadata->getClassName());
        return $persister->loadAll();
    }

    /**
     * @param array $criteria
     */
    public function findBy($criterias = [])
    {
        $persister = $this->manager->getTransshipment()->getPersister($this->metadata->getClassName());
        return $persister->load($criterias);
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