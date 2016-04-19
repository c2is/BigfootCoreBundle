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

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('bigfoot_locale_flags', array($this, 'localeFlags'), array('is_safe' => array('html'), 'needs_environment' => true)),
        );
    }

    /**
     * @param \Twig_Environment $env
     * @return array
     */
    public function localeFlags(\Twig_Environment $env)
    {
        $locales = array();

        foreach ($this->locales as $key => $config) {
            $locale = array(
                'label' => $config['label'],
            );

            if (isset($config['parameters']['flag'])) {
                $locale['flag'] = $this->asset($config['parameters']['flag'], $env);
            } else {
                $locale['flag'] = $this->asset(sprintf('bundles/bigfootcore/img/flags/%s.gif', $key), $env);
            }

            $locales[$key] = $locale;
        }

        return json_encode($locales);
    }

    /**
     * @param $asset
     *
     * @param \Twig_Environment $env
     * @return mixed
     */
    protected function asset($asset, \Twig_Environment $env)
    {
        return call_user_func($env->getFunction('asset')->getCallable(), $asset);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'locales_flags';
    }
}