<?php

namespace Bigfoot\Bundle\CoreBundle\Utils;

class CommonUtils
{
    /**
     * Copy entire contents of a directory to another
     */
    public static function recurseCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ( $file != '..' )) {
                if (is_dir($src.'/'.$file)) {
                    self::recurseCopy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }

        closedir($dir);
    }
}
