<?php

namespace Bigfoot\Bundle\CoreBundle\Crud\Formatter;

interface FormatterInterface
{
    /**
     * @param $value
     * @param $options
     * @return string
     */
    public function format($value, $options = null);

    /**
     * @return string
     */
    public function getName();
}
