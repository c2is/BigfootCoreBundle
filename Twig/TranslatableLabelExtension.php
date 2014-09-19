<?php

namespace Bigfoot\Bundle\CoreBundle\Twig;

/**
 * Class TranslatableLabelExtension
 * @package Bigfoot\Bundle\CoreBundle\Twig
 */
class TranslatableLabelExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'translatable_label_category' => new \Twig_Filter_Method($this, 'getCategory', array('is_safe' => array('html'))),
        );
    }

    /**
     * @param $labelName
     * @return string
     */
    public function getCategory($labelName)
    {
        $posSecondDot = strpos($labelName, '.', strpos($labelName, '.')+1);
        return $posSecondDot !== false ? substr($labelName, 0, $posSecondDot) : $labelName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'translatable_label_extension';
    }
}
