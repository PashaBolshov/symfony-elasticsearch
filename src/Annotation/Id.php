<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Id extends Parameter
{
    public $name = '_id';
}