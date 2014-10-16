<?php

namespace Bigfoot\Bundle\CoreBundle\Form;

use Bigfoot\Bundle\ContextBundle\Service\ContextService;
use Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabel;
use Bigfoot\Bundle\CoreBundle\Manager\TranslatableLabelManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;

abstract class AbstractTranslatableLabelType extends AbstractType
{
    const PLURAL_FIELD_PREFIX = 'pluralForm';

    /** @var \Bigfoot\Bundle\ContextBundle\Service\ContextService */
    protected $context;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var string */
    protected $defaultLocale;

    /** @var \Bigfoot\Bundle\CoreBundle\Manager\TranslatableLabelManager */
    protected $labelManager;

    /**
     * @param ContextService $context
     * @param EntityManager $em
     * @param string $defaultLocale
     * @param TranslatableLabelManager $labelManager
     */
    public function __construct($context, $em, $defaultLocale, $labelManager)
    {
        $this->context = $context;
        $this->em = $em;
        $this->defaultLocale = $defaultLocale;
        $this->labelManager = $labelManager;
    }

    /**
     * @param TranslatableLabel $label
     * @param Form $form
     */
    protected function managePlural($label, $message, $form, $locale)
    {
        $labelManager = $this->labelManager;
        $explicitRules = array();
        $standardRules = array();
        $labelManager->getPluralForms($message, $standardRules, $explicitRules);

        for ($i = 0; $i < count($standardRules); $i++) {
            $form->add(self::PLURAL_FIELD_PREFIX.$i, $labelManager->getValueFieldType($label), array(
                'required' => false,
                'label' => sprintf('bigfoot_core.plural.standard_%s', $i),
                'data' => $standardRules[$i],
                'mapped' => false,
                'attr' => array(
                    'data-locale' => $locale,
                ),
            ));
        }
        foreach ($explicitRules as $interval => $value) {
            $form->add(self::PLURAL_FIELD_PREFIX.$labelManager->transformInterval($interval), $labelManager->getValueFieldType($label), array(
                'required' => false,
                'label' => sprintf('Value for %s', $interval),
                'data' => $value,
                'mapped' => false,
                'attr' => array(
                    'data-locale' => $locale,
                ),
            ));
        }
    }

    /**
     * @param array $data
     * @param Form $form
     * @return string
     */
    protected function aggregatePluralValues(&$data, $form)
    {
        $labelManager = $this->labelManager;
        $labelValue = '';
        $standardRules = array();
        $explicitRules = array();
        foreach ($data as $property => $value) {
            if (0 === strpos($property, self::PLURAL_FIELD_PREFIX)) {
                $pluralForm = substr($property, strlen(self::PLURAL_FIELD_PREFIX));

                if (is_numeric($pluralForm)) {
                    $standardRules[$pluralForm] = $value;
                } else {
                    $explicitRules[$pluralForm] = sprintf('%s %s', $labelManager->reverseTransformInterval($pluralForm), $value);
                }

                $form->remove($property);
                unset($data[$property]);
            }
        }

        if ($standardRules) {
            $labelValue = implode('|', $standardRules);
            if ($explicitRules) {
                $labelValue .= '|'.implode('|', $explicitRules);
            }
        } elseif ($explicitRules) {
            $labelValue = implode('|', $explicitRules);
        }

        return $labelValue;
    }
}