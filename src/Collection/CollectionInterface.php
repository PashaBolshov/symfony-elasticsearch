<?php

namespace Pavlik\ElasticsearchBundle\Collection;


interface CollectionInterface extends \Iterator, \Countable
{
    const MODE_ENTITY   = '1';
    const MODE_DOCUNENT = '0';

    public function fetch($mode = self::MODE_ENTITY);
}