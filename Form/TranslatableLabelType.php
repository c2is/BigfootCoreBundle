<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 28/01/14
 * Time: 16:27
 */

namespace Bigfoot\Bundle\CoreBundle\Form;

use mageekguy\atoum\tests\units\reports\asynchronous\builder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TranslatableLabelType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $valueType = ($options['data']->getIsMultilines()) ? 'textarea': 'text';

        $builder
            ->add('name', 'text', array(
                'read_only' => true,
            ))
            ->add('description', 'text', array(
                'read_only' => true,
            ));

        if ($options['data']->getIsPluralization()) {
            $pluralValues = explode('|', $options['data']->getValue());
            $i = 0;
            foreach($pluralValues as $pValue) {
                preg_match('`([{[0-9]+}|\[[0-9]+,Inf\[) (.+)`', $pValue, $infos);
                $builder
                    ->add('value_'.$i.'_key', 'hidden', array(
                        'mapped' => false,
                        'data' => $infos[1],
                    ))
                    ->add('value_'.$i.'_value', 'text', array(
                        'mapped' => false,
                        'required' => false,
                        'data' => $infos[2],
                        'label' => 'Value for '.$infos[1],
                    ));
                $i++;
            }
            $builder
                ->add('value', 'hidden')
                ->add('nbPluralValues', 'hidden', array(
                    'mapped' => false,
                    'data' => count($pluralValues),
                ))
            ;
        } else {
            $builder
                ->add('value', $valueType);
        }

        $builder
            ->add('translation', 'label_translatable_entity');

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            try{
                $data = $event->getData();
                if (isset($data['nbPluralValues'])) {
                    $compiledArrayData = array();
                    for ($i=0; $i < $data['nbPluralValues']; $i++) {
                        $compiledArrayData[] = $data['value_'.$i.'_key'].' '.$data['value_'.$i.'_value'];
                    }
                    $data['value'] = implode('|', $compiledArrayData);
                    $event->setData($data);
                }
            } catch (\Exception $e) {
                // Case of a non entity object given to the parent form.
                // Unstranslatable case, throw exception.
                $secondException = new \Exception("The object that was given to the form you wanted to translate isn't an entity one. Untranslatable in this case.", $e->getCode(), $e);
                throw $secondException;
            }

        });

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Bigfoot\Bundle\CoreBundle\Entity\TranslatableLabel'
        ));
    }

    public function getName()
    {
        return 'bigfoot_translatable_label';
    }
}