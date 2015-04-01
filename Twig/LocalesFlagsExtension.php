<?php

namespace Bigfoot\Bundle\CoreBundle\Twig;

/**
 * Class LocalesFlagsExtension
 *
 * @package Bigfoot\Bundle\CoreBundle\Twig
 */
class LocalesFlagsExtension extends \Twig_Extension
{
    /** @var array */
    protected $locales;

    /**
     * @param $locales
     */
    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'localeFlags' => new \Twig_Function_Method($this, 'localeFlags', array('is_safe' => array('html'))),
        );
    }

    /**
     * @return array
     */
    public function localeFlags()
    {
        return $this->locales;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'locales_flags';
    }
}