<?php

namespace Bigfoot\Bundle\CoreBundle\Composer;

use Composer\Script\CommandEvent as Event;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;

class BigfootScriptHandler28 extends ScriptHandler
{
    public static function clearCache(Event $event)
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

    public static function installAssets(Event $event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];
        $webDir = $options['symfony-web-dir'];

        $symlink = '';
        if ($options['symfony-assets-install'] == 'symlink') {
            $symlink = '--symlink ';
        } elseif ($options['symfony-assets-install'] == 'relative') {
            $symlink = '--symlink --relative ';
        }

        if (!is_dir($webDir)) {
            echo 'The symfony-web-dir ('.$webDir.') specified in composer.json was not found in '.getcwd().', can not install assets.'.PHP_EOL;

            return;
        }

        static::executeCommand($event, $appDir, 'assets:install '.$symlink.escapeshellarg($webDir));
        static::executeCommand($event, $appDir, 'assetic:dump --env=admin_dev');
    }
}
