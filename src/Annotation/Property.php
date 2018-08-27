<?php

namespace Pavlik\ElasticsearchBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
final class Property
{
    public $name;

    public $type;

    public $options = [];
}