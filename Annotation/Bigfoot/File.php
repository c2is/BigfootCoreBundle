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
    private $filePathProperty;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        if (isset($options['filePathProperty'])) {
            $this->filePathProperty = $options['filePathProperty'];
        } else {
            throw new \Exception('BigfootFileAnnotation : filePathProperty is a mandatory field');
        }
    }

    public function getFilePathProperty()
    {
        return $this->filePathProperty;
    }


}