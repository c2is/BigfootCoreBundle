<?php

namespace Bigfoot\Bundle\CoreBundle\Annotation\Bigfoot;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class File
{
    /** @var string */
    private $relatedProperty;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        if (isset($options['relatedProperty'])) {
            $this->relatedProperty = $options['relatedProperty'];
        } else {
            throw new \Exception('BigfootFileAnnotation : relatedProperty is a mandatory field');
        }
    }

    public function getRelatedProperty()
    {
        return $this->relatedProperty;
    }


}