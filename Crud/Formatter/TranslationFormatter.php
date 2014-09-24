<?php

namespace Bigfoot\Bundle\CoreBundle\Crud\Formatter;

use Symfony\Component\Translation\Translator;

/**
 * Class TranslationFormatter
 * @package Bigfoot\Bundle\CoreBundle\Crud\Formatter
 */
class TranslationFormatter implements FormatterInterface
{
    /** @var \Symfony\Component\Translation\Translator */
    private $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param $value
     * @return string
     */
    public function format($value)
    {
        return $this->translator->trans($value);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'trans';
    }
}
