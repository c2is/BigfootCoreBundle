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
    public function bigfootFile($entity, $field, $absolute = false)
    {
        $this->fileManager->initialize($entity, $field);

        if ($absolute === true) {
            return $this->fileManager->getFileAbsolutePath();
        } else {
            return $this->fileManager->getFilePath();
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_crud_formatter';
    }
}
