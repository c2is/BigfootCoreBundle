<?php

namespace Bigfoot\Bundle\CoreBundle\Crud\Formatter;

/**
 * Class BoolFormatter
 * @package Bigfoot\Bundle\CoreBundle\Crud\Formatter
 */
class BoolFormatter implements FormatterInterface
{
    /**
     * @param       $value
     * @param array $options
     *
     * @return string
     */
    public function format($value, array $options = [])
    {
        return '<label><input class="ace ace-switch ace-switch-7" type="checkbox" '. ($value ? 'checked="checked"' : '') . ' disabled="disabled"/><span class="lbl"></span></label>';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bool';
    }
}
