<?php

namespace Bigfoot\Bundle\CoreBundle\Crud\Formatter;

interface FormatterInterface
{
    /**
     * @param $value
     * @return string
     */
    public function format($value);

    /**
     * @return string
     */
    public function getName();
}
