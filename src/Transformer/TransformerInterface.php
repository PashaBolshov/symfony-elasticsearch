<?php

namespace Pavlik\ElasticsearchBundle\Transformer;

use Pavlik\ElasticsearchBundle\Annotation\Metadata;
use Elastica\Document;

interface TransformerInterface
{
    /**
     * @param object $documet
     * @param Metadata $metadata
     * @return Document
     */
    public function transformToDoc($object, Metadata $metadata);

    /**
     * @param Document $documet
     * @param Metadata $metadata
     * @return object
     */
    public function transformToEntity(Document $documet, Metadata $metadata);
}