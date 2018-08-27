<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Embedded
{
    public $class;

    public $prefix;
}