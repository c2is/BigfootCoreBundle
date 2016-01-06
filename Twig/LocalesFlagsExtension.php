<?php

namespace Bigfoot\Bundle\CoreBundle\Twig;

/**
 * Class LocalesFlagsExtension
 *
 * @package Bigfoot\Bundle\CoreBundle\Twig
 */
class LocalesFlagsExtension extends \Twig_Extension
{
    /** @var \Twig_Environment */
    protected $twig;

    /** @var array */
    protected $locales;

    /**
     * @param $locales
     */
    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    public function initRuntime(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('bigfoot_locale_flags', array($this, 'localeFlags'), array('is_safe' => array('html'))),
        );
    }

    /**
     * @return array
     */
    public function localeFlags()
    {
        $locales = array();

        foreach ($this->locales as $key => $config) {
            $locale = array(
                'label' => $config['label'],
            );

            if (isset($config['parameters']['flag'])) {
                $locale['flag'] = $this->asset($config['parameters']['flag']);
            } else {
                $locale['flag'] = $this->asset(sprintf('bundles/bigfootcore/img/flags/%s.gif', $key));
            }

            $locales[$key] = $locale;
        }

        return json_encode($locales);
    }

    /**
     * @param $asset
     *
     * @return mixed
     */
    protected function asset($asset)
    {
        return call_user_func($this->twig->getFunction('asset')->getCallable(), $asset);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'locales_flags';
    }
}