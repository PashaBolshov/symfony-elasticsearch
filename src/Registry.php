<?php

namespace Pavlik\ElasticsearchBundle;

abstract class Registry
{
    /**
     * @var string 
     */
    protected $name;

    /**
     * @var array
     */
    protected $clients = [];

    /**
     * @var array
     */
    protected $managers = [];

    /**
     * @var string
     */
    protected $defaultClient;

    /**
     * @var string
     */
    protected $defaultManager;

    /**
     * @param string $name
     * @param array $clients
     * @param array $manages
     * @param string $defaultClient
     * @param string $defaultManager
     */
    public function __construct($name, array $clients, array $managers, string $defaultClient, string $defaultManager)
    {
        $this->name = $name;
        $this->clients = $clients;
        $this->managers = $managers;
        $this->defaultClient = $defaultClient;
        $this->defaultManager = $defaultManager;
    }

    /**
     * @return object
     */
    abstract protected function getService($serviceName);

    /**
     * @return Manager
     * @throws \InvalidArgumentException
     */
    public function getManager($name = null)
    {
        if (null === $name) {
            $name = $this->defaultManager;
        }

        if (!isset($this->managers[$name])) {
            throw new \InvalidArgumentException(sprintf('Symfony ElasticSearch %s Manager named "%s" does not exist.', $this->name, $name));
        }

        return $this->getService($this->managers[$name]);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getClient($name = null)
    {
        if (null === $name) {
            $name = $this->defaultClient;
        }

        if (!isset($this->clients[$name])) {
            throw new \InvalidArgumentException(sprintf('Symfony ElasticSearch %s Client named "%s" does not exist.', $this->name, $name));
        }

        return $this->getService($this->clients[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository($persistentObjectName, $persistentManagerName = null)
    {
        return $this->getManager($persistentManagerName)->getRepository($persistentObjectName);
    }
}