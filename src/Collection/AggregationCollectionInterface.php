<?php

namespace Pavlik\ElasticsearchBundle\Collection;


interface AggregationCollectionInterface extends CollectionInterface
{
    /**
     * @return bool
     */
    public function hasAggregations();

    /**
     * @return array
     */
    public function getAggregations();

    /**
     * @param string $name
     * @return array
     */
    public function getAggregation($name);
}