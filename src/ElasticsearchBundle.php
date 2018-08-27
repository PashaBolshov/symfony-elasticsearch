<?php

namespace Pavlik\ElasticsearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Pavlik\ElasticsearchBundle\DependencyInjection\PavlikElasticsearchExtension;

class ElasticsearchBundle extends Bundle 
{
    /**
     * Returns the bundle's container extension.
     *
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension()
    {
        if( is_null($this->extension) ) {
            $this->extension = new PavlikElasticsearchExtension();
        }  

        return $this->extension;
    }
}