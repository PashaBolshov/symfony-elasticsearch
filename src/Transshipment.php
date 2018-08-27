<?php

namespace Pavlik\ElasticsearchBundle;

use Pavlik\ElasticsearchBundle\Persister\Persister;
use Elastica\Bulk;
use Elastica\Bulk\ResponseSet;
use Elastica\Bulk\Response;

class Transshipment
{
    CONST STATE_INDEX  = 'index';
    CONST STATE_DELETE = 'delete';
    CONST STATE_UPDATE = 'update';

    /**
     * @var Manager 
     */
    protected $manager;

    /**
     * @var Persister
     */
    protected $persisters = [];

    /**
     * @var array
     */
    protected $entitiesStates = [];

    /**
     * @var array
     */
    protected $toIndex = [];

    /**
     * @var array
     */
    protected $toUpdate = [];

    /**
     * @var array
     */
    protected $toDelete = [];

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;   
    }

    /**
     * @param string $className
     * @return Persister
     */
    public function getPersister($className)
    {
        if( isset($this->persisters[$className]) ) {
            return $this->persisters[$className];
        }

        $this->persisters[$className] = new Persister(
            $this->manager->loadClassMetadata($className),
            $this->manager
        );

        return $this->persisters[$className];
    }

    /**
     * @var object $entity
     */
    public function index($entity)
    {
        $eid = spl_object_hash($entity);
        $this->toIndex[$eid] = $entity;

        $this->entitiesStates[$eid] = self::STATE_INDEX;
    }

    /**
     * @var object $entity
     */
    public function delete($entity)
    {
        $eid = spl_object_hash($entity);
        $this->toDelete[$eid] = $entity;

        $this->entitiesStates[$eid] = self::STATE_DELETE;
    }

    /**
     * @var object $entity
     */
    public function update($entity)
    {
        $eid = spl_object_hash($entity);
        $this->toUpdate[$eid] = $entity;
        $this->entitiesStates[$eid] = self::STATE_UPDATE;
    }

    public function flush()
    {
        $actions = [];
        foreach( $this->entitiesStates as $eid => $state ) {
            switch($state) {
                case self::STATE_INDEX:
                    $entity = $this->toIndex[$eid];
                    break;
                
                case self::STATE_UPDATE:
                    $entity = $this->toUpdate[$eid];
                    break;

                case self::STATE_DELETE:
                    $entity = $this->toDelete[$eid];
                    break;
            }

            $class = get_class($entity);
            $persister = $this->getPersister($class);

            switch($state) {
                case self::STATE_INDEX:
                    $action = $persister->getIndexAction($entity);
                    break;
                
                case self::STATE_UPDATE:
                    $action = $persister->getUpdateAction($entity);
                    break;

                case self::STATE_DELETE:
                    $action = $persister->getDeleteAction($entity);
                    break;
            }

            $actions[] = $action;
        }

        $bulk = $this->getBulk();
        $bulk->addActions($actions);
        $responses = $bulk->send();

        $responses->rewind();
        foreach( $this->entitiesStates as $eid => $state ) {
            $response = $responses->current();
            switch($state) {
                case self::STATE_INDEX:
                case self::STATE_UPDATE:
                    $entity   = $this->toIndex[$eid];
                    $class    = get_class($entity);
                    $metadata = $this->manager->loadClassMetadata($class);

                    if( $property = $metadata->getEntityIdProperty() ) {
                        $metadata->setEntityPropertyValue($entity, $property, $response->getData()['_id']);
                    }

                    if( $property = $metadata->getEntityVersionProperty() ) {
                        $metadata->setEntityPropertyValue($entity, $property, $response->getData()['_version']);
                    }
                    break;
            }

            $responses->next();
        }
       
        $this->toIndex        = [];
        $this->toDelete       = [];
        $this->toUpdate       = [];
        $this->entitiesStates = [];
    }

    /**
     * @return Bulk
     */
    protected function getBulk()
    {
        return new Bulk($this->manager->getClient());
    }
}