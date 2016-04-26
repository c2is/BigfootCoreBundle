<?php

namespace Bigfoot\Bundle\CoreBundle\Translation;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Gestion des convertions de traductions
 */
class Convert
{
    /** @var Kernel */
    private $kernel = null;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Recherche récursive de tous les fichiers de traduction dans le répertoire donné
     *
     * @param string $dir
     * @param array $translationFiles
     */
    protected function findTranslationFiles($dir, array &$translationFiles)
    {
        if (is_dir($dir) === false) {
            return null;
        }
        foreach (scandir($dir) as $fileOrDir) {
            $fullFileOrDir = $dir . DIRECTORY_SEPARATOR . $fileOrDir;
            if (is_dir($fullFileOrDir)) {
                if ($fileOrDir != '.' && $fileOrDir != '..') {
                    $this->findTranslationFiles($fullFileOrDir, $translationFiles);
                }
            } else {
                $pathinfo = pathinfo($fullFileOrDir);
                if ($pathinfo['extension'] == 'yml') {
                    $translationFiles[] = $fullFileOrDir;
                }
            }
        }
    }

    /**
     * Retourne la clef de traduction complète
     *
     * @param string $baseKey
     * @param string $key
     * @return string
     */
    protected function getTranslationKey($baseKey, $key)
    {
        return ($baseKey == null) ? $key : $baseKey . '.' . $key;
    }

    /**
     * Parse un tableau de traductions et écrit dans $parsedTranslations sous la forme [foo.bar] => 'translation'
     *
     * @param string $key
     * @param string $translations
     * @param array $parsedTranslations
     */
    protected function parseTranslations($key, $translations, array &$parsedTranslations)
    {
        if (is_array($translations)) {
            foreach ($translations as $translationKey => $translation) {
                if (is_array($translation)) {
                    $this->parseTranslations($this->getTranslationKey($key, $translationKey), $translation, $parsedTranslations);
                } else {
                    $parsedTranslations[$this->getTranslationKey($key, $translationKey)] = $translation;
                }
            }
        } else {
            $parsedTranslations[$key] = $translation;
        }
    }

    /**
     * Converti les fichiers de traductions "Symfony2 style" vers les fichiers "Bigfoot style"
     *
     * @param array $bundles
     * @param ProgressBar $progress
     */
    public function symfony2ToBigFoot(array $bundles = array(), $progress = null)
    {
        $newTranslationFiles = array();
        $bundles = (count($bundles) == 0) ? $this->kernel->getBundles() : $bundles;

        foreach ($bundles as $bundle) {
            $translationsPath = $bundle->getPath() . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'translations';
            $translationFiles = array();
            $this->findTranslationFiles($translationsPath, $translationFiles);

            foreach ($translationFiles as $translationFile) {
                $pathinfo = pathinfo($translationFile);
                $domain = substr($pathinfo['filename'], 0, strrpos($pathinfo['filename'], '.'));
                $lang = substr($pathinfo['filename'], strrpos($pathinfo['filename'], '.') + 1);
                $parsedTranslations = array();
                $translations = Yaml::parse(file_get_contents($translationFile));

                // les clefs qui ont au moins 3 niveaux (par exemple foo.bar.baz) sont gérées dans un fichier foo.bar.yml
                // les clefs qui ont moins de 3 niveaux sont gérées dans default.yml
                foreach ($translations as $key1 => $translation1) {
                    if (is_array($translation1)) {
                        foreach ($translation1 as $key2 => $translation2) {
                            $fileName = $this->getTranslationKey($key1, $key2) . '.yml';
                            if (array_key_exists($fileName, $newTranslationFiles) == false) {
                                $newTranslationFiles[$fileName] = array();
                            }

                            if (is_array($translation2)) {
                                $parsedTranslations = array();
                                $this->parseTranslations(null, $translation2, $parsedTranslations);

                                foreach ($parsedTranslations as $parsedKey => $parsedTranslation) {
                                    if (array_key_exists($parsedKey, $newTranslationFiles[$fileName]) == false) {
                                        $newTranslationFiles[$fileName][$parsedKey] = array();
                                        if ($domain != 'messages') {
                                            $newTranslationFiles[$fileName][$parsedKey]['domain'] = $domain;
                                        }
                                        $newTranslationFiles[$fileName][$parsedKey]['value'] = array();
                                    }

                                    $newTranslationFiles[$fileName][$parsedKey]['value'][$lang] = $parsedTranslation;

                                    // multiline
                                    if (strpos($parsedTranslation, "\n")) {
                                        $newTranslationFiles[$fileName][$parsedKey]['multiline'] = true;
                                    }

                                    // plural
                                    if (strpos($parsedTranslation, "|")) {
                                        $newTranslationFiles[$fileName][$parsedKey]['plural'] = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($progress instanceof ProgressBar) {
                $progress->advance();
            }
        }

        // écriture des fichiers
        foreach ($newTranslationFiles as $newTranslationFile => $newTranslations) {
            $fileName = 'app' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'translatable_label' . DIRECTORY_SEPARATOR . $newTranslationFile;
            file_put_contents($fileName, Yaml::dump($newTranslations, 3));
        }
    }

}
