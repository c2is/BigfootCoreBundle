<?php

namespace Bigfoot\Bundle\CoreBundle\Form\Type;

use Bigfoot\Bundle\CoreBundle\Form\EventListener\TranslationSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

            if ($filter['type'] == 'choice' || $filter['type'] == 'repositoryMethod' || ($filter['type'] == 'referer' && $options['type'] == 'choice')) {
                $builder->add(
                    $filter['name'],
                    ChoiceType::class,
                    array(
                        'choices'  => array_flip($options['choices']),
                        'required' => false,
                        'data'     => $value,
                        'label'    => $filter['placeholder'],
                    )
                );
            }

            if ($filter['type'] == 'entity') {
                if (!empty($value)) {
                    $value = $this->manager->getEntity($filter, $value);
                }

                $builder->add(
                    $filter['name'],
                    EntityType::class,
                    array(
                        'class'         => $options['class'],
                        'property'      => $options['property'],
                        'required'      => false,
                        'label'         => $filter['placeholder'],
                        'data'          => $value,
                        'query_builder' => isset($options['query_builder']) ? $options['query_builder'] : null,
                    )
                );
            }

            if (($filter['type'] == 'referer' && $options['type'] == 'text') || $filter['type'] == 'search') {
                $builder->add(
                    $filter['name'],
                    TextType::class,
                    array(
                        'required' => false,
                        'data'     => $value,
                        'label'    => $filter['placeholder'],
                    )
                );
            }

            if ($filter['type'] == 'date_min') {
                $builder->add(
                    $filter['name'],
                    DateType::class,
                    array(
                        'required' => false,
                        'data'     => $value,
                        'label'    => $filter['placeholder'],
                    )
                );
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
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
