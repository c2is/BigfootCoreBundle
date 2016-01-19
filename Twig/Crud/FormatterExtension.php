<?php

namespace Bigfoot\Bundle\CoreBundle\Twig\Crud;

use Bigfoot\Bundle\CoreBundle\Crud\Formatter\Loader;

/**
 * Class FormatterExtension
 * @package Bigfoot\Bundle\CoreBundle\Twig\Crud
 */
class FormatterExtension extends \Twig_Extension
{
    /** @var Loader */
    private $loader;

    /**
     * @param Loader $formattersLoader
     */
    public function __construct($formattersLoader)
    {
        $this->loader = $formattersLoader;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'bigfoot_crud_formatter' => new \Twig_Filter_Method($this, 'format'),
            'bigfoot_crud_formatter_raw' => new \Twig_Filter_Method($this, 'format', array('is_safe' => 'html')),
        );
    }

    /**
     * @param $value
     * @param array $formatters
     * @return string
     */
    public function format($value, $formatters = array())
    {
        return $this->loader->applyFormatters($value, $formatters);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_crud_formatter';
    }
}
