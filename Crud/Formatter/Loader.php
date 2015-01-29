<?php

namespace Bigfoot\Bundle\CoreBundle\Crud\Formatter;

/**
 * Class Loader
 * @package Bigfoot\Bundle\CoreBundle\Crud\Formatter
 */
class Loader
{
    /** @var array */
    private $formatters = array();

    /**
     * @param FormatterInterface $formatter
     */
    public function addFormatter(FormatterInterface $formatter)
    {
        $this->formatters[$formatter->getName()] = $formatter;
    }

    /**
     * @param string $value
     * @param array $formattersToCall
     * @return string
     */
    public function applyFormatters($value, array $formattersToCall = array())
    {
        foreach ($formattersToCall as $key => $options) {
            if (is_array($options)) {
                $formatterToCall = $key;
            } else {
                $formatterToCall = $options;
                $options = array();
            }
            /** @var FormatterInterface $formatter */
            foreach ($this->formatters as $formatterName => $formatter) {
                if ($formatterName == $formatterToCall) {
                    $value = $formatter->format($value, $options);
                    break;
                }
            }
        }

        return $value;
    }
}
