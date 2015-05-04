<?php

namespace Bigfoot\Bundle\CoreBundle\Manager;

use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Interval;

class TranslatableLabelManager
{
    /** @var string */
    protected $cacheDir;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    protected $filesystem;

    /**
     * @param string $cacheDir
     * @param Filesystem $filesystem
     */
    public function __construct($cacheDir, $filesystem)
    {
        $this->cacheDir = $cacheDir;
        $this->filesystem = $filesystem;
    }

    /**
     * @param TranslatableLabel $label
     * @return string
     */
    public function getValueFieldType($label)
    {
        if ($label->isRichtext()) {
            return 'bigfoot_richtext';
        }

        return $label->isMultiline() ? 'textarea' : 'text';
    }

    /**
     * @param string $interval
     * @return string
     */
    public function transformInterval($interval)
    {
        return str_replace(array('[', ']', '-', '{', '}', ','), array('______', '_____', '____', '___', '__', '_'), $interval);
    }

    /**
     * @param string $interval
     * @return string
     */
    public function reverseTransformInterval($interval)
    {
        return str_replace(array('______', '_____', '____', '___', '__', '_'), array('[', ']', '-', '{', '}', ','), $interval);
    }

    /**
     * @param $message
     * @param array $standardRules
     * @param array $explicitRules
     * @return array
     */
    public function getPluralForms($message, &$standardRules = array(), &$explicitRules = array())
    {
        $parts = explode('|', $message);
        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match('/^(?P<interval>'.Interval::getIntervalRegexp().')\s*(?P<message>.*?)$/x', $part, $matches)) {
                $explicitRules[$matches['interval']] = $matches['message'];
            } elseif (preg_match('/^\w+\:\s*(.*?)$/', $part, $matches)) {
                $standardRules[] = $matches[1];
            } else {
                $standardRules[] = $part;
            }
        }

        return $standardRules;
    }

    public function clearTranslationCache()
    {
        $fs = $this->filesystem;
        $finder = new Finder();
        try {
            $fs->remove($finder->in(sprintf('%s/../*/translations/', $this->cacheDir))->name('catalogue.*.php'));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
