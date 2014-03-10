<?php

namespace Bigfoot\Bundle\CoreBundle\Util;

class StringManager
{
    /**
     * Camelize string
     */
    public static function camelize($string)
    {
        return preg_replace_callback('/(^|_|\.)+(.)/', function ($match) { return ('.' === $match[1] ? '_' : '').strtoupper($match[2]); }, $string);
    }
}
