<?php

namespace Bigfoot\Bundle\CoreBundle\Crud\Formatter;

/**
 * Class DateFormatter
 * @package Bigfoot\Bundle\CoreBundle\Crud\Formatter
 */
class DateFormatter implements FormatterInterface
{
    /**
     * @param $value
     * @return string
     */
    public function format($value)
    {
        if (!$value instanceof \DateTime) {
            return $value;
        }

        return $value->format('d/m/Y');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'date';
    }
}
