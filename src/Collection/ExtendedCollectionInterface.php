<?php

namespace Pavlik\ElasticsearchBundle\Collection;


interface ExtendedCollectionInterface extends CollectionInterface
{
    /**
     * @return bool
     */
    public function hasSuggests();

    /**
     * @return array
     */
    public function getSuggests();

    /**
     * @return bool
     */
    public function hasAggregations();

    /**
     * @return array
     */
    public function getAggregations();

    /**
     * @return int
     */
    public function getTotalHits();

    /**
     * @param string $name
     * @return array
     */
    public function getAggregation($name);
}