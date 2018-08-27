<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Document
{
    public $repository;
}