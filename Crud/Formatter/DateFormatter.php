<?php

namespace Bigfoot\Bundle\CoreBundle\Crud\Formatter;

/**
 * Class DateFormatter
 * @package Bigfoot\Bundle\CoreBundle\Crud\Formatter
 */
class DateFormatter implements FormatterInterface
{

    /**
     * @var String dateFormat
     */
    private $dateFormat;

    public function __construct($dateFormat)
    {
        $this->dateFormat = $dateFormat;
    }

    /**
     * @param $value
     * @param array $options
     * @return string
     */
    public function format($value, array $options = array())
    {
        if (!$value instanceof \DateTime) {
            return $value;
        }

        if (array_key_exists('format', $options)) {
            return $value->format($options['format']);
        }

        return $value->format($this->dateFormat);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'date';
    }
}
