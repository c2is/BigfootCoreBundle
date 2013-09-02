<?php

namespace Bigfoot\Bundle\CoreBundle\Composer;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;

class BigfootScriptHandler extends ScriptHandler
{
    public static function clearCache(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not clear the cache.'.PHP_EOL;

            return;
        }

        $arguments = array('--no-warmup');
        if (!$event->isDevMode()) {
            $arguments = array('--env=prod');
        }

        $cmd = sprintf('cache:clear %s', implode(' ', $arguments));

        static::executeCommand($event, $appDir, $cmd, $options['process-timeout']);
    }
}
