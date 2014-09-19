<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Bigfoot\Bundle\CoreBundle\Form\EventListener\TranslationSubscriber;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Bigfoot\Bundle\CoreBundle\Manager\FilterManager;

/**
 * Class FilterType
 *
 * @package Bigfoot\Bundle\CoreBundle\Form\Type
 */
class FilterType extends AbstractType
{
    /**
     * @var FilterManager
     */
    private $manager;

    /**
     * Constructor
     *
     * @param FilterManager $manager
     */
    public function __construct(FilterManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $filters = $options['filters'];
        $entity  = $options['entity'];

        $datas   = $this->manager->getSessionFilter($entity);

        foreach ($filters as $filter) {
            $options = isset($filter['options']) ? $filter['options'] : array();
            $value   = isset($datas[$filter['name']]) ? $datas[$filter['name']] : null;

            if ($filter['type'] == 'choice' || ($filter['type'] == 'referer' && $options['type'] == 'choice')) {
                $builder->add(
                    $filter['name'],
                    'choice',
                    array(
                        'choices'  => $options['choices'],
                        'required' => false,
                        'data'     => $value,
                        'label'    => $filter['placeholder'],
                    )
                );
            } elseif ($filter['type'] == 'entity') {
                if (!empty($value)) {
                    $value = $this->manager->getEntity($filter, $value);
                }

                $builder->add(
                    $filter['name'],
                    'entity',
                    array(
                        'class'    => $options['class'],
                        'property' => $options['property'],
                        'required' => false,
                        'label'    => $filter['placeholder'],
                        'data'     => $value,
                    )
                );
            } elseif (($filter['type'] == 'referer' && $options['type'] == 'text') || $filter['type'] == 'search') {
                $builder->add(
                    $filter['name'],
                    'text',
                    array(
                        'required' => false,
                        'data'     => $value,
                        'label'    => $filter['placeholder'],
                    )
                );
            } elseif ($filter['type'] == 'repositoryMethod') {
                $builder->add(
                    $filter['name'],
                    'choice',
                    array(
                        'required' => false,
                        'choices'  => $this->manager->getEntityManager()->getRepository($filter['referer'])->{$options['choicesMethod']}(),
                        'data'     => $value,
                        'label'    => $filter['placeholder'],
                    )
                );
            }
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => null,
                'filters'    => array(),
                'entity'     => null
            )
        );
    }

    /**
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options = array())
    {
        return $options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_core_filter_type';
    }
}
