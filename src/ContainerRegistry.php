<?php

namespace Pavlik\ElasticsearchBundle;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerRegistry extends Registry
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @param array $clients
     * @param array $manages
     * @param string $defaultClient
     * @param string $defaultManager
     */
    public function __construct(ContainerInterface $container, array $clients, array $managers, string $defaultClient, string $defaultManager)
    {
        $this->container = $container;
        parent::__construct('ContainerRegistry', $clients, $managers, $defaultClient, $defaultManager);
    }

    /**
     * {@inheritdoc}
     * @throws 
     */
    protected function getService($serviceName)
    {
        return $this->container->get($serviceName);
    }
}