<?php

namespace Bigfoot\Bundle\CoreBundle\Crud\Formatter;

interface FormatterInterface
{
    /**
     * @param $value
     * @param array $options
     * @return string
     */
    public function format($value, array $options = array());

    /**
     * @return string
     */
    public function getName();
}
