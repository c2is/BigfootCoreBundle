<?php

namespace Bigfoot\Bundle\CoreBundle\Twig;

class LocalesFlagsExtension extends \Twig_Extension
{
    protected $locales;

    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    public function getFunctions()
    {
        return array(
            'localeFlags' => new \Twig_Function_Method($this, 'localeFlags', array('is_safe' => array('html'))),
        );
    }

    public function localeFlags()
    {
        return array_keys($this->locales);
    }

    public function getName()
    {
        return 'locales_flags';
    }
}