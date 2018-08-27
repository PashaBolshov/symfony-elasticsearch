<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Join
{
    public $property;

    public $relations;
}