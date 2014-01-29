<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 28/01/14
 * Time: 16:27
 */

namespace Bigfoot\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TranslatableLabelType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'read_only' => true,
            ))
            ->add('description', 'text', array(
                'read_only' => true,
            ))
            ->add('value', 'text')
            ->add('translation', 'translatable_entity');

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