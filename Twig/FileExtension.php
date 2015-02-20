<?php

namespace Bigfoot\Bundle\CoreBundle\Twig;

use Bigfoot\Bundle\CoreBundle\Manager\FileManager;

/**
 * Class FileExtension
 * @package Bigfoot\Bundle\CoreBundle\Twig
 */
class FileExtension extends \Twig_Extension
{
    /** @var FileManager */
    private $fileManager;

    /**
     * @param Loader $formattersLoader
     */
    public function __construct($fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'bigfoot_file' => new \Twig_Filter_Method($this, 'bigfootFile'),
        );
    }

    /**
     * @param mixed $entity
     * @param strign $field
     * @return string
     */
    public function bigfootFile($entity, $filePathField, $absolute = false)
    {
        if ($absolute === true) {
            return $this->fileManager->getFileAbsolutePath($entity, $filePathField);
        } else {
            return $this->fileManager->getFilePath($entity, $filePathField);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_file_filter';
    }
}
